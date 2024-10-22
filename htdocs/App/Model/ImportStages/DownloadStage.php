<?php declare(strict_types = 1);

namespace App\Model\ImportStages;

use App\Model\ImportStages\Exceptions\DownloadStageException;
use App\Services\RepositoryConfiguration;
use App\Services\S3Service;
use League\Pipeline\StageInterface;

readonly class DownloadStage implements StageInterface
{

    public function __construct(protected S3Service $s3Service, protected RepositoryConfiguration $configuration)
    {
    }

    public function __invoke(mixed $payload): mixed
    {
        try {
            $this->s3Service->getObject($payload->getHerbarium()->getBucket(), $payload->getOriginalFilename(), $this->configuration->getImportTempPath($payload));
            $payload->setOriginalFileAt($this->s3Service->getObjectOriginalTimestamp($payload->getHerbarium()->getBucket(), $payload->getOriginalFilename()));

        } catch (\Throwable $exception) {
            throw new DownloadStageException('download original file error (' . $exception->getMessage() . ')');
        }

        return $payload;
    }

}
