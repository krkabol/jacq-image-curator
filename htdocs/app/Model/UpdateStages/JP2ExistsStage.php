<?php

declare(strict_types=1);

namespace app\Model\UpdateStages;

use app\Model\Database\Entity\Photos;
use app\Services\S3Service;
use app\Services\StorageConfiguration;
use League\Pipeline\StageInterface;


class JP2ExistsException extends BaseStageException
{

}

class JP2ExistsStage implements StageInterface
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
        /** @var Photos $payload */
        if (!$this->s3Service->objectExists($this->configuration->getJP2Bucket(), $payload->getJp2Filename())) {
            throw new JP2ExistsException("Expected JP2 file is missing");
        }
        return $payload;
    }


}
