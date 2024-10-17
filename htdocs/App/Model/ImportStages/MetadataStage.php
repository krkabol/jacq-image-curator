<?php declare(strict_types = 1);

namespace App\Model\ImportStages;

use App\Model\Database\Entity\Photos;
use App\Model\ImportStages\Exceptions\MetadataStageException;
use App\Services\ImageService;
use App\Services\RepositoryConfiguration;
use Imagick;
use League\Pipeline\StageInterface;

class MetadataStage implements StageInterface
{

    protected Photos $item;

    public function __construct(protected readonly RepositoryConfiguration $storageConfiguration, protected readonly ImageService $imageService)
    {
    }

    protected function readDimensions(Imagick $imagick): Imagick
    {
        $this->item->setWidth($imagick->getImageWidth());
        $this->item->setHeight($imagick->getImageHeight());

        return $imagick;
    }

    public function __invoke(mixed $payload): mixed
    {
        try {
            $this->item = $payload;
            $imagick = $this->imageService->createImagick($this->storageConfiguration->getImportTempPath($this->item));
            $this->readDimensions($imagick);
            $imagick->destroy();
            unset($imagick);

            $exifData = exif_read_data($this->storageConfiguration->getImportTempPath($this->item));
            if ($exifData !== false) {
                $this->item->setExif($exifData);
            }
            return $this->item;
        } catch (\Throwable $e) {
            throw new MetadataStageException('problem with metadata detection: ' . $e->getMessage());
        }
    }

}
