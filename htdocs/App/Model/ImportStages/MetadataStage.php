<?php declare(strict_types=1);

namespace App\Model\ImportStages;

use App\Model\Database\Entity\Photos;
use App\Model\ImportStages\Exceptions\MetadataStageException;
use App\Services\ImageService;
use App\Services\RepositoryConfiguration;
use Imagick;
use League\Pipeline\StageInterface;
use Throwable;

class MetadataStage implements StageInterface
{

    protected Photos $item;

    public function __construct(protected readonly RepositoryConfiguration $storageConfiguration, protected readonly ImageService $imageService)
    {
    }


    public function __invoke(mixed $payload): mixed
    {
        try {
            $this->item = $payload;
            $imagick = $this->imageService->createImagick($this->storageConfiguration->getImportTempPath($this->item));
            $this->readDimensions($imagick);
            $this->item->setIdentify($this->imageService->readIdentify($imagick));
            $imagick->destroy();
            unset($imagick);

            $this->item->setExif($this->imageService->readExif($this->storageConfiguration->getImportTempPath($this->item)));
            return $this->item;
        } catch (Throwable $e) {
            throw new MetadataStageException('problem with metadata detection: ' . $e->getMessage());
        }
    }

    protected function readDimensions(Imagick $imagick): Imagick
    {
        $this->item->setWidth($imagick->getImageWidth());
        $this->item->setHeight($imagick->getImageHeight());

        return $imagick;
    }

}
