<?php

declare(strict_types=1);

namespace App\Model\ImportStages;

use App\Model\Database\Entity\Photos;
use App\Services\S3Service;
use App\Services\StorageConfiguration;
use Exception;
use League\Pipeline\StageInterface;


class DownloadStageException extends ImportStageException
{

}

readonly class DownloadStage implements StageInterface
{


    public function __construct(protected S3Service $s3Service, protected StorageConfiguration $configuration)
    {
    }

    public function __invoke($payload)
    {
        try {
            /** @var Photos $payload */
            $this->s3Service->getObject($this->configuration->getCuratorBucket(), $payload->getOriginalFilename(), $this->configuration->getImportTempPath($payload));
            $payload->setOriginalFileAt($this->s3Service->getObjectOriginalTimestamp($this->configuration->getCuratorBucket(), $payload->getOriginalFilename()));

        } catch (Exception $exception) {
            throw new DownloadStageException("download original file error (" . $exception->getMessage() . ")");
        }
        return $payload;
    }
}
