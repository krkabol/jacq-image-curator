<?php

declare(strict_types=1);

namespace App\Model\ImportStages;

use App\Model\Database\Entity\Photos;
use App\Services\ImageService;
use App\Services\StorageConfiguration;
use Exception;
use Imagick;
use League\Pipeline\StageInterface;

class DimensionsStageException extends ImportStageException
{

}

class DimensionsStage implements StageInterface
{
    protected Photos $item;

    public function __construct(protected readonly StorageConfiguration $storageConfiguration, protected readonly ImageService $imageService)
    {
    }

    public function __invoke($payload)
    {
        try {
            $this->item = $payload;
            $imagick = $this->imageService->createImagick($this->storageConfiguration->getImportTempPath($this->item));
            $this->readDimensions($imagick);
            $imagick->destroy();
            unset($imagick);
            return $this->item;
        } catch (Exception $e) {
            throw new DimensionsStageException('problem with dimensions: ' . $e->getMessage());
        }
    }

    protected function readDimensions(Imagick $imagick): Imagick
    {
        $this->item->setWidth($imagick->getImageWidth());
        $this->item->setHeight($imagick->getImageHeight());
        return $imagick;
    }

}