<?php

declare(strict_types=1);

namespace app\Model;

use app\Services\S3Service;
use app\Services\StorageConfiguration;
use app\Services\TempDir;

class PhotoOfSpecimenFactory
{

    protected S3Service $s3Service;
    protected TempDir $tempDir;
    protected StorageConfiguration $storageConfiguration;


    public function __construct(S3Service $s3Service, TempDir $tempDir, StorageConfiguration $storageConfiguration)
    {
        $this->s3Service = $s3Service;
        $this->tempDir = $tempDir;
        $this->storageConfiguration = $storageConfiguration;
    }

    public function create(string $bucket, string $key): PhotoOfSpecimen
    {
        return new PhotoOfSpecimen($bucket, $key, $this->s3Service, $this->tempDir, $this->storageConfiguration);
    }

}
