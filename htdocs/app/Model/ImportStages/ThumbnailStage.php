<?php

declare(strict_types=1);

namespace app\Model\ImportStages;

use app\Model\Database\Entity\Photos;
use app\Services\ImageService;
use app\Services\StorageConfiguration;
use Exception;
use Imagick;
use League\Pipeline\StageInterface;

class ThumbnailStageException extends ImportStageException
{

}

class ThumbnailStage implements StageInterface
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
            $this->createThumbnail($imagick);
            return $this->item;
        } catch (ThumbnailStageException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ThumbnailStageException('thumbnail error: ' . $e->getMessage());
        }
    }

    protected function createThumbnail(Imagick $imagick): void
    {
       $imagick = $this->imageService->resizeImage($imagick, 1800);
        $imagick->setImageFormat('jpg');
        $imagick->setImageCompressionQuality(80);
        $this->item->setThumbnail($imagick->getImagesBlob());
        $imagick->destroy();
        $imagick->clear();
        unset($imagick);
    }

}
