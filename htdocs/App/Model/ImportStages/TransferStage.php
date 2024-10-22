<?php declare(strict_types = 1);

namespace App\Model\ImportStages;

use App\Model\Database\Entity\Photos;
use App\Model\ImportStages\Exceptions\TransferStageException;
use App\Services\AppConfiguration;
use App\Services\RepositoryConfiguration;
use App\Services\S3Service;
use League\Pipeline\StageInterface;

class TransferStage implements StageInterface
{

    protected Photos $item;

    public function __construct(protected readonly S3Service $s3Service, protected readonly RepositoryConfiguration $repositoryConfiguration, protected readonly AppConfiguration $appConfiguration)
    {
    }

    protected function uploadJp2toRepository(): void
    {
        try {
            $this->s3Service->putJp2IfNotExists($this->repositoryConfiguration->getImageServerBucket(), $this->repositoryConfiguration->createS3Jp2Name($this->item), $this->repositoryConfiguration->getImportTempJp2Path($this->item));
            $this->item->setJP2Filename($this->repositoryConfiguration->createS3Jp2Name($this->item));
        } catch (\Throwable $exception) {
            throw new TransferStageException('jp2 upload error (' . $exception->getMessage() . ')');
        }
    }

    protected function uploadTiftoRepository(): void
    {
        try {
            $this->s3Service->putTiffIfNotExists($this->repositoryConfiguration->getArchiveBucket(), $this->repositoryConfiguration->createS3TifName($this->item), $this->repositoryConfiguration->getImportTempPath($this->item));
            $this->item->setArchiveFilename($this->repositoryConfiguration->createS3TifName($this->item));
        } catch (\Throwable $exception) {
            throw new TransferStageException('tiff upload error (' . $exception->getMessage() . ')');
        }
    }

    protected function deleteTifFromCuratorBucket(): void
    {
        try {
            $this->s3Service->deleteObject($this->item->getHerbarium()->getBucket(), $this->item->getOriginalFilename());
        } catch (\Throwable $exception) {
            throw new TransferStageException('deleting tif from curatorBucket error (' . $exception->getMessage() . ')');
        }
    }

    public function __invoke(mixed $payload): mixed
    {
        $this->item = $payload;
        $this->uploadJp2toRepository();
        $this->uploadTiftoRepository();
        if ($this->appConfiguration->isProduction()) {
            $this->deleteTifFromCuratorBucket();
        }

        return $payload;
    }

}
