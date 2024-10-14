<?php declare(strict_types = 1);

namespace App\Model\ImportStages;

use App\Model\Database\Entity\Photos;
use App\Model\ImportStages\Exceptions\ThumbnailStageException;
use App\Services\ImageService;
use App\Services\RepositoryConfiguration;
use Imagick;
use League\Pipeline\StageInterface;

class ThumbnailStage implements StageInterface
{

    protected Photos $item;

    public function __construct(protected readonly RepositoryConfiguration $repositoryConfiguration, protected readonly ImageService $imageService)
    {
    }

    protected function createThumbnail(Imagick $imagick): void
    {
        $imagick = $this->imageService->resizeImage($imagick, $this->repositoryConfiguration->getPreviewSize());
        $imagick->setImageFormat('jpg');
        $imagick->setImageCompressionQuality($this->repositoryConfiguration->getPreviewQuality());
        $this->item->setThumbnail($imagick->getImagesBlob());
        $imagick->destroy();
        unset($imagick);
    }

    public function __invoke(mixed $payload): mixed
    {
        try {
            $this->item = $payload;
            $imagick = $this->imageService->createImagick($this->repositoryConfiguration->getImportTempPath($this->item));
            $this->createThumbnail($imagick);

            return $this->item;
        } catch (\Throwable $e) {
            throw new ThumbnailStageException('thumbnail error: ' . $e->getMessage());
        }
    }

}
