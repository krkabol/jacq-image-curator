<?php declare(strict_types = 1);

namespace App\Model\ImportStages;

use App\Model\ImportStages\Exceptions\ConvertStageException;
use App\Services\S3Service;
use App\Services\StorageConfiguration;
use Imagick;
use League\Pipeline\StageInterface;

readonly class ConvertStage implements StageInterface
{

    public function __construct(protected S3Service $s3Service, protected StorageConfiguration $storageConfiguration)
    {
    }

    public function __invoke(mixed $payload): mixed
    {
//TODO compression ratio as config
        try {
            $imagick = new Imagick($this->storageConfiguration->getImportTempPath($payload));
            $imagick->setImageFormat('jp2');
            $imagick->setImageCompressionQuality(100);//$this->storageConfiguration->getJP2Quality());
            $imagick->writeImage($this->storageConfiguration->getImportTempJP2Path($payload));
            $imagick->destroy();
            unset($imagick);
            $payload->setJp2FileSize(filesize($this->storageConfiguration->getImportTempJP2Path($payload)));
        } catch (\Throwable $exception) {
            throw new ConvertStageException('unable convert to JP2 (' . $exception->getMessage() . '): ' . $payload->getId());
        }

        return $payload;
    }

}
