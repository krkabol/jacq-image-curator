<?php

declare(strict_types=1);

namespace app\Model\ImportStages;

use app\Model\Database\Entity\Photos;
use app\Services\S3Service;
use app\Services\StorageConfiguration;
use Exception;
use League\Pipeline\StageInterface;

class ConvertStageException extends ImportStageException
{

}

class ConvertStage implements StageInterface
{

    public function __construct(protected readonly S3Service $s3Service, protected readonly StorageConfiguration $storageConfiguration)
    {
    }


    public function __invoke($payload)
    {
        /** @var Photos $payload */
        try {
            $imagick = new \Imagick($this->storageConfiguration->getImportTempPath($payload));
            $imagick->setImageFormat('jp2');
            $imagick->setImageCompressionQuality(100);//$this->storageConfiguration->getJP2Quality());
            $imagick->writeImage($this->storageConfiguration->getImportTempJP2Path($payload));
            $imagick->destroy();
            $imagick->clear();
            unset($imagick);
            $payload->setJP2FileSize(filesize($this->storageConfiguration->getImportTempJP2Path($payload)));
        } catch (Exception $exception) {
            throw new ConvertStageException("unable convert to JP2 (" . $exception->getMessage() . "): " . $payload->getId());
        }
        return $payload;
    }
}
