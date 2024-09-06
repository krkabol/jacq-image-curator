<?php

declare(strict_types=1);

namespace app\Model\ImportStages;

use app\Model\PhotoOfSpecimen;
use app\Services\S3Service;
use app\Services\StorageConfiguration;
use League\Pipeline\StageInterface;

class ArchiveImportStageException extends ImportStageException
{

}

class ArchiveStage implements StageInterface
{
    protected S3Service $s3Service;
    protected StorageConfiguration $configuration;

    public function __construct(S3Service $s3Service, StorageConfiguration $configuration)
    {
        $this->s3Service = $s3Service;
        $this->configuration = $configuration;
    }

    public function __invoke($payload)
    {
        try {
            /** @var PhotoOfSpecimen $payload */
            $this->s3Service->copyObjectIfNotExists($payload->getObjectKey(), $this->configuration->getCuratorBucket(), $this->configuration->getArchiveBucket());
        } catch (\Exception $exception) {
            throw new ArchiveImportStageException("tiff upload error (" . $exception->getMessage() . "): " . $payload->getObjectKey());
        }
        return $payload;
    }


}
