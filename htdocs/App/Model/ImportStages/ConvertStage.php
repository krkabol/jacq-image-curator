<?php declare(strict_types = 1);

namespace App\Model\ImportStages;

use App\Model\ImportStages\Exceptions\ConvertStageException;
use App\Services\ImageService;
use App\Services\RepositoryConfiguration;
use App\Services\S3Service;
use League\Pipeline\StageInterface;

readonly class ConvertStage implements StageInterface
{

    public function __construct(protected S3Service $s3Service, protected RepositoryConfiguration $repositoryConfiguration, protected ImageService $imageService)
    {
    }

    public function __invoke(mixed $payload): mixed
    {
        try {
            $imagick = $this->imageService->createImagick($this->repositoryConfiguration->getImportTempPath($payload));
            $imagick->setImageFormat('jp2');
            $imagick->setImageCompressionQuality($this->repositoryConfiguration->getJp2Quality());
            $imagick->writeImage($this->repositoryConfiguration->getImportTempJp2Path($payload));
            $imagick->destroy();
            unset($imagick);
            $payload->setJp2FileSize(filesize($this->repositoryConfiguration->getImportTempJp2Path($payload)));
        } catch (\Throwable $exception) {
            throw new ConvertStageException('unable convert to JP2 (' . $exception->getMessage() . '): ' . $payload->getId());
        }

        return $payload;
    }

}
