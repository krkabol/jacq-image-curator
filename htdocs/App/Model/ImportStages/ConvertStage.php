<?php declare(strict_types = 1);

namespace App\Model\ImportStages;

use App\Model\ImportStages\Exceptions\ConvertStageException;
use App\Services\S3Service;
use App\Services\RepositoryConfiguration;
use Imagick;
use League\Pipeline\StageInterface;

readonly class ConvertStage implements StageInterface
{

    public function __construct(protected S3Service $s3Service, protected RepositoryConfiguration $storageConfiguration)
    {
    }

    public function __invoke(mixed $payload): mixed
    {
        try {
            $imagick = new Imagick($this->storageConfiguration->getImportTempPath($payload));
            $imagick->setImageFormat('jp2');
            $imagick->setImageCompressionQuality($this->storageConfiguration->getJp2Quality());
            $imagick->writeImage($this->storageConfiguration->getImportTempJp2Path($payload));
            $imagick->destroy();
            unset($imagick);
            $payload->setJp2FileSize(filesize($this->storageConfiguration->getImportTempJp2Path($payload)));
        } catch (\Throwable $exception) {
            throw new ConvertStageException('unable convert to JP2 (' . $exception->getMessage() . '): ' . $payload->getId());
        }

        return $payload;
    }

}
