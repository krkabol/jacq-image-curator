<?php declare(strict_types = 1);

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

    public function __construct(protected readonly RepositoryConfiguration $repositoryConfiguration, protected readonly ImageService $imageService)
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
            $imagick = $this->imageService->createImagick($this->repositoryConfiguration->getImportTempPath($this->item));
            $this->readDimensions($imagick);
            $this->item->setIdentify($this->imageService->readIdentify($imagick));
            $this->item->setExif($this->imageService->readExif($imagick));
            $imagick->destroy();
            unset($imagick);

            return $this->item;
        } catch (Throwable $e) {
            throw new MetadataStageException('problem with metadata detection: ' . $e->getMessage());
        }
    }

}
