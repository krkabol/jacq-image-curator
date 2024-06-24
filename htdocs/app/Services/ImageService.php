<?php

declare(strict_types=1);

namespace app\Services;

use app\Model\PhotoOfSpecimenFactory;
use app\Model\Stages\BarcodeStage;
use app\Model\Stages\BaseStageException;
use app\Model\Stages\StageFactory;
use League\Pipeline\Pipeline;

class ImageService
{
    const LIMIT = 100;
    protected S3Service $S3Service;
    protected PhotoOfSpecimenFactory $photoOfSpecimenFactory;
    protected StageFactory $stageFactory;
    protected StorageConfiguration $storageConfiguration;

    public function __construct(S3Service $S3Service, PhotoOfSpecimenFactory $photoOfSpecimenFactory, StageFactory $stageFactory, StorageConfiguration $storageConfiguration)
    {
        $this->S3Service = $S3Service;
        $this->photoOfSpecimenFactory = $photoOfSpecimenFactory;
        $this->stageFactory = $stageFactory;
        $this->storageConfiguration = $storageConfiguration;
    }


    public function proceedNewImages(): array
    {
        return $this->runPipeline($this->fileProcessingPipeline());
    }

    public function fileProcessingPipeline(): Pipeline
    {
        return $this->controlPipeline()
            ->pipe($this->stageFactory->createConvertStage())
            ->pipe($this->stageFactory->createArchiveStage())
            ->pipe($this->stageFactory->createRegisterStage())
            ->pipe($this->stageFactory->createCleanupStage());
    }

    public function controlPipeline(): Pipeline
    {
        return (new Pipeline())
            ->pipe($this->stageFactory->createFilenameControlStage())
            ->pipe($this->stageFactory->createNoveltyControlStage())
            ->pipe($this->stageFactory->createDimensionsStage())
            ->pipe(new BarcodeStage);
    }

    public function proceedDryrun(): array
    {
        return $this->runPipeline($this->controlPipeline());
    }

    protected function runPipeline(Pipeline $pipeline): array
    {
        $success = [];
        $error = [];
        $i = 0;
        foreach ($this->S3Service->listObjects($this->storageConfiguration->getNewBucket()) as $file) {
            try {
                $photo = $this->photoOfSpecimenFactory->create($this->storageConfiguration->getNewBucket(), $file);
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
