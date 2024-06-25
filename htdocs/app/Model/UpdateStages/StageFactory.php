<?php

declare(strict_types=1);

namespace App\Model\UpdateStages;

use App\Model\Database\EntityManager;
use app\Services\S3Service;
use app\Services\StorageConfiguration;
use app\Services\TempDir;

class StageFactory
{

    protected S3Service $s3Service;
    protected TempDir $tempDir;
    protected EntityManager $entityManager;
    protected StorageConfiguration $storageConfiguration;


    public function __construct(S3Service $s3Service, TempDir $tempDir, EntityManager $entityManager, StorageConfiguration $storageConfiguration)
    {
        $this->s3Service = $s3Service;
        $this->tempDir = $tempDir;
        $this->entityManager = $entityManager;
        $this->storageConfiguration = $storageConfiguration;
    }


    public function createJP2ExistsStage(): JP2ExistsStage
    {
        return new JP2ExistsStage($this->s3Service, $this->storageConfiguration);
    }

    public function createUpdateRecordStage(): UpdateRecordStage
    {
        return new UpdateRecordStage($this->entityManager, $this->storageConfiguration, $this->s3Service);
    }

    public function createCleanupStage(): CleanupStage
    {
        return new CleanupStage($this->tempDir);
    }

    public function createDimensionsStage(): DimensionsStage
    {
        return new DimensionsStage($this->tempDir);
    }

    public function createFilenameControlStage(): FilenameControlStage
    {
        return new FilenameControlStage($this->entityManager);
    }

    public function createBarcodeStage(): BarcodeStage
    {
        return new BarcodeStage($this->tempDir);
    }

    public function createDownloadJP2Stage(): DownloadJP2Stage
    {
        return new DownloadJP2Stage($this->s3Service, $this->storageConfiguration, $this->tempDir);
    }
}
