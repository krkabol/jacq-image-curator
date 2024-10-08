<?php

declare(strict_types=1);

namespace app\Model\UpdateStages;

use app\Model\Database\Entity\Photos;
use app\Services\S3Service;
use app\Services\StorageConfiguration;
use app\Services\TempDir;
use Exception;
use League\Pipeline\StageInterface;


class DownloadJP2StageException extends BaseStageException
{

}
/** @deprecated  */
class DownloadJP2Stage implements StageInterface
{
    protected S3Service $s3Service;
    protected StorageConfiguration $configuration;
    protected TempDir $tempDir;

    public function __construct(S3Service $s3Service, StorageConfiguration $configuration, TempDir $tempDir)
    {
        $this->s3Service = $s3Service;
        $this->configuration = $configuration;
        $this->tempDir = $tempDir;
    }

    public function __invoke($payload)
    {
        try {
            /** @var Photos $payload */
            $this->s3Service->getObject($this->configuration->getJP2Bucket(), $payload->getJp2Filename(), $this->tempDir->getPath($payload->getJp2Filename()));

        } catch (Exception $exception) {
            throw new DownloadJP2StageException("download temp file error (" . $exception->getMessage().")");
        }
        return $payload;
    }
}
