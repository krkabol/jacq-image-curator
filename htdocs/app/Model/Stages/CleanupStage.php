<?php

declare(strict_types=1);

namespace app\Model\Stages;


use app\Model\PhotoOfSpecimen;
use app\Services\S3Service;
use app\Services\StorageConfiguration;
use League\Pipeline\StageInterface;

class CleanupStageException extends BaseStageException
{

}

class CleanupStage implements StageInterface
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
            $payload->unsetImagick();
            unlink($payload->getTempfile());
            $this->s3Service->deleteObject($this->configuration->getNewBucket(), $payload->getObjectKey());
        } catch (\Exception $exception) {
            throw new CleanupStageException("cleanup error (" . $exception->getMessage() . "): " . $payload->getObjectKey());
        }
        return $payload;
    }

}
