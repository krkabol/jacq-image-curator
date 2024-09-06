<?php

declare(strict_types=1);

namespace app\Services;

use App\Model\Database\EntityManager;
use app\Model\PhotoOfSpecimenFactory;
use app\Model\MigrationStages\StageFactory;
use App\UI\Admin\Test\TestPresenter;
use League\Pipeline\Pipeline;
use Nette\Neon\Exception;

class TestService
{
    const int LIMIT = 10;
    public function __construct(protected readonly WebDir $webDir,protected readonly S3Service $S3Service,protected readonly  PhotoOfSpecimenFactory $photoOfSpecimenFactory,protected readonly  StageFactory $stageFactory,protected readonly  StorageConfiguration $storageConfiguration,protected readonly  ImageService $imageService,protected readonly EntityManager $entityManager)
    {
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
            $this->S3Service->putTiffIfNotExists($this->storageConfiguration->getCuratorBucket(), strtolower($file), $testDataDir . DIRECTORY_SEPARATOR . $file);
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
