<?php declare(strict_types = 1);

namespace App\Model\MigrationStages;

use App\Model\Database\EntityManager;
use App\Model\MigrationStages\Exceptions\UpdateRecordException;
use App\Services\S3Service;
use App\Services\StorageConfiguration;
use League\Pipeline\StageInterface;

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

    public function __invoke(mixed $payload): mixed
    {
        try {
            $payload
                ->setArchiveFileSize($this->s3Service->getObjectSize($this->configuration->getArchiveBucket(), $payload->getArchiveFilename()))
                ->setJP2FileSize($this->s3Service->getObjectSize($this->configuration->getJP2Bucket(), $payload->getJp2Filename()));
        } catch (\Throwable $exception) {
            throw new UpdateRecordException('db update record error (' . $exception->getMessage() . ')');
        }

        return $payload;
    }

}
