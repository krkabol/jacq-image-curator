<?php

declare(strict_types=1);

namespace app\Model\Stages;

use app\Model\PhotoOfSpecimen;
use app\Services\S3Service;
use app\Services\StorageConfiguration;
use League\Pipeline\StageInterface;

class NoveltyControlException extends BaseStageException
{

}

class NoveltyControlStage implements StageInterface
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
        /** @var PhotoOfSpecimen $payload */
        if ($this->s3Service->objectExists($this->configuration->getArchiveBucket(), $payload->getObjectKey())) {
            throw new NoveltyControlException("Archive master TIF already exists: " . $payload->getObjectKey());
        }
        return $payload;
    }


}
