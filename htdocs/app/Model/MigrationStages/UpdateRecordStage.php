<?php

declare(strict_types=1);

namespace app\Model\MigrationStages;

use app\Model\Database\Entity\Photos;
use App\Model\Database\EntityManager;
use app\Services\S3Service;
use app\Services\StorageConfiguration;
use Exception;
use League\Pipeline\StageInterface;

class UpdateRecordStageException extends BaseStageException
{

}

class UpdateRecordStage implements StageInterface
{

    protected EntityManager $entityManager;
    protected StorageConfiguration $configuration;
    protected S3Service $s3Service;


    public function __construct(EntityManager $entityManager, StorageConfiguration $configuration, S3Service $s3Service)
    {
        $this->entityManager = $entityManager;
        $this->configuration = $configuration;
        $this->s3Service = $s3Service;
    }

    public function __invoke($payload)
    {
        /** @var Photos $payload */
        try {
            $payload
                ->setFinalized(true)
                ->setArchiveFileSize($this->s3Service->getObjectSize($this->configuration->getArchiveBucket(), $payload->getArchiveFilename()))
                ->setJP2FileSize($this->s3Service->getObjectSize($this->configuration->getJP2Bucket(), $payload->getJp2Filename()));
        } catch (Exception $exception) {
            throw new UpdateRecordStageException("db update record error (" . $exception->getMessage() . ")");
        }
        return $payload;
    }
}
