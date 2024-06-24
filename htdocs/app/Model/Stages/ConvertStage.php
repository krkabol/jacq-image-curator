<?php

declare(strict_types=1);

namespace app\Model\Stages;

use app\Model\PhotoOfSpecimen;
use app\Services\S3Service;
use app\Services\StorageConfiguration;
use Exception;
use League\Pipeline\StageInterface;

class ConvertStageException extends BaseStageException
{

}

class ConvertStage implements StageInterface
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
        try {
            $imagick = $payload->getImagick();
            $imagick->setImageFormat('jp2');
            $imagick->setCompressionQuality($this->configuration->getJP2Quality());
            $imagick->writeImage($payload->getJP2Fullname());

            $this->s3Service->putJP2Overwrite($this->configuration->getJP2Bucket(), $this->configuration->getJP2ObjectKey($payload->getObjectKey()), $payload->getJP2Fullname());

            unlink($payload->getJP2Fullname());
        } catch (Exception $exception) {
            throw new ConvertStageException("unable convert to JP2 (" . $exception->getMessage() . "): " . $payload->getObjectKey());
        }
        return $payload;
    }


}
