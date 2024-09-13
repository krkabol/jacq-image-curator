<?php

declare(strict_types=1);

namespace app\Model\ImportStages;

use App\Model\Database\EntityManager;
use app\Services\S3Service;
use app\Services\StorageConfiguration;
use app\Services\TempDir;

readonly class StageFactory
{

    public function __construct(protected S3Service $s3Service, protected TempDir $tempDir, protected EntityManager $entityManager, protected StorageConfiguration $storageConfiguration)
    {
    }

    public function createDownloadStage(): DownloadStage
    {
        return new DownloadStage($this->s3Service, $this->storageConfiguration);
    }

    public function createBarcodeStage(): BarcodeStage
    {
        return new BarcodeStage($this->storageConfiguration);
    }

    public function createConvertStage(): ConvertStage
    {
        return new ConvertStage($this->s3Service, $this->storageConfiguration);
    }

    public function createDuplicityStage(): DuplicityStage
    {
        return new DuplicityStage($this->entityManager);
    }

    public function createTransferStage(): TransferStage
    {
        return new TransferStage($this->s3Service, $this->storageConfiguration);
    }
}
