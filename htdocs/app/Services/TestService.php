<?php

declare(strict_types=1);

namespace app\Services;

use app\Model\PhotoOfSpecimenFactory;
use app\Model\Stages\BarcodeStage;
use app\Model\Stages\BaseStageException;
use app\Model\Stages\StageFactory;
use app\UI\Test\TestPresenter;
use League\Pipeline\Pipeline;

class TestService
{
    protected WebDir $webDir;

    const LIMIT = 10;
    protected S3Service $S3Service;
    protected PhotoOfSpecimenFactory $photoOfSpecimenFactory;
    protected StageFactory $stageFactory;
    protected StorageConfiguration $storageConfiguration;
    protected ImageService $imageService;

    public function __construct(WebDir $webDir,S3Service $S3Service, PhotoOfSpecimenFactory $photoOfSpecimenFactory, StageFactory $stageFactory, StorageConfiguration $storageConfiguration, ImageService $imageService)
    {
        $this->webDir = $webDir;
        $this->S3Service = $S3Service;
        $this->photoOfSpecimenFactory = $photoOfSpecimenFactory;
        $this->stageFactory = $stageFactory;
        $this->storageConfiguration = $storageConfiguration;
        $this->imageService = $imageService;
    }

    public function initialize(): void
    {
        foreach ($this->storageConfiguration->getAllBuckets() as $bucket) {
            $this->S3Service->createBucket($bucket);
        }

        $testDataDir = $this->webDir->getPath('data');
        foreach (TestPresenter::TEST_FILES as $file) {
            $this->S3Service->putTiffIfNotExists($this->storageConfiguration->getNewBucket(), strtolower($file), $testDataDir . DIRECTORY_SEPARATOR . $file);
        }
    }

    public function proceedExistingImages(): array
    {
        return $this->runPipeline($this->migrationPipeline(),$this->storageConfiguration->getArchiveBucket());
    }

    protected function migrationPipeline(): Pipeline
    {
        return (new Pipeline())
            ->pipe($this->stageFactory->createNotInDatabaseStage())
            ->pipe($this->stageFactory->createFilenameControlStage())
            ->pipe($this->stageFactory->createDimensionsStage())
            ->pipe(new BarcodeStage)
            ->pipe($this->stageFactory->createRegisterStage())
            ->pipe($this->stageFactory->createCleanupStage());
    }

    public function proceedNewImages(): array
    {
        return $this->runPipeline($this->imageService->fileProcessingPipeline(),$this->storageConfiguration->getNewBucket());
    }

    protected function runPipeline(Pipeline $pipeline, $location): array
    {
        $success = [];
        $error = [];
        $i = 0;
        foreach ($this->S3Service->listObjects($location) as $file) {
            try {
                $photo = $this->photoOfSpecimenFactory->create($location, $file);
                $pipeline->process($photo);
                $success[$file] = "OK";
                $i++;
            } catch (BaseStageException $e) {
                $error[$file] = $e->getMessage();
            }
            if ($i >= self::LIMIT) {
                break;
            }
        }
        return [$success, $error];
    }
}
