<?php

declare(strict_types=1);

namespace app\Model\ImportStages;

use app\Model\PhotoOfSpecimen;
use app\Services\S3Service;
use app\Services\StorageConfiguration;
use League\Pipeline\StageInterface;

class DimensionImportStageException extends ImportStageException
{

}

class DimensionsStage implements StageInterface
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
        try {
            /** @var PhotoOfSpecimen $payload */
            $imagick = $payload->getImagick();
            $payload->setWidth($imagick->getImageWidth());
            $payload->setHeight($imagick->getImageHeight());
        } catch (\Exception $exception) {
            throw new DimensionImportStageException("dimensions error (" . $exception->getMessage() . "): " . $payload->getObjectKey());
        }
        return $payload;
    }
}
