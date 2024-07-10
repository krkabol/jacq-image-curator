<?php

declare(strict_types=1);

namespace app\Services;

use App\Model\Database\EntityManager;
use app\Model\PhotoOfSpecimenFactory;
use app\Model\UpdateStages\BaseStageException;
use app\Model\UpdateStages\StageFactory;
use app\UI\Test\TestPresenter;
use League\Pipeline\Pipeline;
use Nette\Neon\Exception;

class TestService
{
    protected WebDir $webDir;

    const LIMIT = 10;
    protected S3Service $S3Service;
    protected PhotoOfSpecimenFactory $photoOfSpecimenFactory;
    protected StageFactory $stageFactory;
    protected StorageConfiguration $storageConfiguration;
    protected ImageService $imageService;

    protected EntityManager $entityManager;

    public function __construct(WebDir $webDir,S3Service $S3Service, PhotoOfSpecimenFactory $photoOfSpecimenFactory, StageFactory $stageFactory, StorageConfiguration $storageConfiguration, ImageService $imageService, EntityManager $entityManager)
    {
        $this->webDir = $webDir;
        $this->S3Service = $S3Service;
        $this->photoOfSpecimenFactory = $photoOfSpecimenFactory;
        $this->stageFactory = $stageFactory;
        $this->storageConfiguration = $storageConfiguration;
        $this->imageService = $imageService;
        $this->entityManager = $entityManager;
    }

    /** @deprecated
     * used during initial stage for MinIO temp repository
     */
    public function initialize(): void
    {
        throw new Exception("do not initialize repository!");

        foreach ($this->storageConfiguration->getAllBuckets() as $bucket) {
            $this->S3Service->createBucket($bucket);
        }

        $testDataDir = $this->webDir->getPath('data');
        foreach (TestPresenter::TEST_FILES as $file) {
            $this->S3Service->putTiffIfNotExists($this->storageConfiguration->getNewBucket(), strtolower($file), $testDataDir . DIRECTORY_SEPARATOR . $file);
        }
    }

     public function migrationPipeline(): Pipeline
    {
        return (new Pipeline())
            ->pipe($this->stageFactory->createJP2ExistsStage())
            ->pipe($this->stageFactory->createFilenameControlStage())
            ->pipe($this->stageFactory->createDimensionsStage())
            ->pipe($this->stageFactory->createBarcodeStage())
            ->pipe($this->stageFactory->createUpdateRecordStage());
    }

}
