<?php declare(strict_types=1);

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
            //TODO solve JSOn escaping
//            $this->item->setIdentify($this->convertArrayEncoding($imagick->identifyImage(true), 'UTF-8', 'UTF-8//IGNORE'));
            $imagick->destroy();
            unset($imagick);
            //TODO solve JSOn escaping
//            $exifData = exif_read_data($this->storageConfiguration->getImportTempPath($this->item));
//            if ($exifData !== false) {
//                $this->item->setExif($this->convertArrayEncoding($exifData, 'UTF-8', 'UTF-8//IGNORE'));
//            } else {
//                $this->item->setExif([]);
//            }
            return $this->item;
        } catch (\Throwable $e) {
            throw new MetadataStageException('problem with metadata detection: ' . $e->getMessage());
        }
    }

    protected function convertArrayEncoding($input, $from_encoding, $to_encoding)
    {
        $output = [];

        foreach ($input as $key => $value) {
            // Convert key if needed (optional, if keys are in a specific encoding)
            $newKey = is_string($key) ? iconv($from_encoding, $to_encoding, $key) : $key;

            if (is_array($value)) {
                // Rekurzivní volání pro vnořená pole
                $output[$newKey] = $this->convertArrayEncoding($value, $from_encoding, $to_encoding);
            } elseif (is_string($value)) {
                // Použij iconv na hodnotu, pokud je typu string
                $output[$newKey] = iconv($from_encoding, $to_encoding, $value);
            } else {
                // Ostatní typy hodnot necháme beze změny
                $output[$newKey] = $value;
            }
        }

        return $output;
    }

    protected function utf8ize( $mixed ) {
        if (is_array($mixed)) {
            foreach ($mixed as $key => $value) {
                $mixed[$key] = $this->utf8ize($value);
            }
        } elseif (is_string($mixed)) {
            return mb_convert_encoding($mixed, "UTF-8", "UTF-8");
        }
        return $mixed;
    }

    function removeInvalidChars( $text) {
        $regex = '/( [\x00-\x7F] | [\xC0-\xDF][\x80-\xBF] | [\xE0-\xEF][\x80-\xBF]{2} | [\xF0-\xF7][\x80-\xBF]{3} ) | ./x';
        return preg_replace($regex, '$1', $text);
    }

}
