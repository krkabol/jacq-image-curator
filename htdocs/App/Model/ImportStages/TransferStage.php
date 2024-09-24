<?php declare(strict_types = 1);

namespace App\Model\ImportStages;

use App\Model\Database\Entity\Photos;
use App\Model\ImportStages\Exceptions\TransferStageException;
use App\Services\S3Service;
use App\Services\StorageConfiguration;
use League\Pipeline\StageInterface;

class TransferStage implements StageInterface
{

    protected Photos $item;

    public function __construct(protected readonly S3Service $s3Service, protected readonly StorageConfiguration $storageConfiguration)
    {
    }

    protected function uploadJp2toRepository(): void
    {
        try {
            $this->s3Service->putJP2IfNotExists($this->storageConfiguration->getJP2Bucket(), $this->storageConfiguration->createS3JP2Name($this->item), $this->storageConfiguration->getImportTempJP2Path($this->item));
            $this->item->setJP2Filename($this->storageConfiguration->createS3JP2Name($this->item));
        } catch (\Throwable $exception) {
            throw new TransferStageException('jp2 upload error (' . $exception->getMessage() . ')');
        }
    }

    protected function uploadTiftoRepository(): void
    {
        try {
            $this->s3Service->putTiffIfNotExists($this->storageConfiguration->getArchiveBucket(), $this->storageConfiguration->createS3TIFName($this->item), $this->storageConfiguration->getImportTempPath($this->item));
            $this->item->setArchiveFilename($this->storageConfiguration->createS3TIFName($this->item));
        } catch (\Throwable $exception) {
            throw new TransferStageException('tiff upload error (' . $exception->getMessage() . ')');
        }
    }

    protected function deleteTiffromCuratorBucket(): void
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

//        $this->deleteTIFfromCuratorBucket();
        return $payload;
    }

}
