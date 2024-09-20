<?php

declare(strict_types=1);

namespace App\Model\MigrationStages;

use App\Model\Database\EntityManager;
use App\Services\S3Service;
use App\Services\StorageConfiguration;
use App\Services\TempDir;
use GuzzleHttp\Client;

readonly class StageFactory
{

    public function __construct(protected S3Service $s3Service, protected TempDir $tempDir, protected EntityManager $entityManager, protected StorageConfiguration $storageConfiguration, protected Client $client)
    {
    }


    public function createJP2ExistsStage(): JP2ExistsStage
    {
        return new JP2ExistsStage($this->storageConfiguration, $this->client);
    }

    public function createUpdateRecordStage(): UpdateRecordStage
    {
        return new UpdateRecordStage($this->entityManager, $this->storageConfiguration, $this->s3Service);
    }

    public function createDimensionsStage(): DimensionsStage
    {
        return new DimensionsStage($this->client, $this->storageConfiguration);
    }

    public function createFilenameControlStage(): FilenameControlStage
    {
        return new FilenameControlStage($this->entityManager, $this->storageConfiguration);
    }

    /** @deprecated */
    public function createDownloadJP2Stage(): DownloadJP2Stage
    {
        return new DownloadJP2Stage($this->s3Service, $this->storageConfiguration, $this->tempDir);
    }
}
