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
    public const string ENCODING = "UTF-8";

    public function __construct(protected readonly RepositoryConfiguration $storageConfiguration, protected readonly ImageService $imageService)
    {
    }

    protected function readDimensions(Imagick $imagick): Imagick
    {
        $this->item->setWidth($imagick->getImageWidth());
        $this->item->setHeight($imagick->getImageHeight());

        return $imagick;
    }

    protected function readIdentify(Imagick $imagick): Imagick
    {
        $identify = $imagick->identifyImage(true);
        $identify['rawOutput'] = $this->parseIdentify($identify['rawOutput']);
        $encoding = mb_detect_encoding($this->recursiveArrayToString($identify));
        $clean = $this->convertArrayEncoding($identify, self::ENCODING, $encoding);
        $this->item->setIdentify($clean);
        return $imagick;
    }

    protected function readExif(string $path): void
    {
        $exifData = exif_read_data($path);
        if ($exifData !== false) {
            $encoding = mb_detect_encoding($this->recursiveArrayToString($exifData));
            $clean = $this->convertArrayEncoding($exifData, self::ENCODING, $encoding);
            $this->item->setExif($clean);
        } else {
            $this->item->setExif([]);
        }
    }

    public function __invoke(mixed $payload): mixed
    {
        try {
            $this->item = $payload;
            $imagick = $this->imageService->createImagick($this->storageConfiguration->getImportTempPath($this->item));
            $this->readDimensions($imagick);
            $this->readIdentify($imagick);
            $imagick->destroy();
            unset($imagick);

            $this->readExif($this->storageConfiguration->getImportTempPath($this->item));
            return $this->item;
        } catch (Throwable $e) {
            throw new MetadataStageException('problem with metadata detection: ' . $e->getMessage());
        }
    }

    protected function convertArrayEncoding($input, $to_encoding, $from_encoding)
    {
        $output = [];

        foreach ($input as $key => $value) {
            $newKey = is_string($key) ? mb_convert_encoding($key, $to_encoding, $from_encoding) : $key;
            if (is_array($value)) {
                $output[$newKey] = $this->convertArrayEncoding($value, $to_encoding, $from_encoding);
            } elseif (is_string($value)) {
                //https://stackoverflow.com/questions/17499955/understanding-what-u0000-is-in-php-json-and-getting-rid-of-it
                $output[$newKey] = str_replace(chr(0), "", mb_convert_encoding($value, $to_encoding, $from_encoding));

            } else {
                $output[$newKey] = $value;
            }
        }
        return $output;
    }

    protected function recursiveArrayToString($array): string
    {
        $result = '';

        foreach ($array as $value) {
            if (is_array($value)) {
                $result .= $this->recursiveArrayToString($value);
            } elseif (is_string($value)) {
                $result .= $value;
            }
        }

        return $result;
    }

    /**
     * from https://www.php.net/manual/en/imagick.identifyimage.php
     * $identify = $this->parseIdentify($identify['rawOutput']);
     */
    protected function parseIdentify($info)
    {
        $lines = explode("\n", $info);

        $outputs = [];
        $output = [];
        $keys = [];

        $currSpaces = 0;
        $raw = false;

        foreach ($lines as $line) {
            $trimLine = trim($line);

            if (empty($trimLine)) continue;

            if ($raw) {
                preg_match('/^[0-9]+:\s/', $trimLine, $match);

                if (!empty($match)) {
                    $regex = '/([\d]+):';
                    $regex .= '\s(\([\d|\s]{1,3},[\d|\s]{1,3},[\d|\s]{1,3},[\d|\s]{1,3}\))';
                    $regex .= '\s(#\w+)';
                    $regex .= '\s(srgba\([\d|\s]{1,3},[\d|\s]{1,3},[\d|\s]{1,3},[\d|\s|.]+\)|\w+)/';

                    preg_match($regex, $trimLine, $matches);
                    array_shift($matches);

                    $output['Image'][$raw][] = $matches;

                    continue;
                } else {
                    $raw = false;
                    array_pop($keys);
                }
            }

            preg_match('/^\s+/', $line, $match);

            $spaces = isset($match[0]) ? strlen($match[0]) : $spaces = 0;
            $parts = preg_split('/:\s/', $trimLine, 2);

            $_key = ucwords($parts[0]);
            $_key = str_replace(' ', '', $_key);
            $_val = isset($parts[1]) ? $parts[1] : [];

            if ($_key == 'Image') {
                if (!empty($output)) {
                    $outputs[] = $output['Image'];
                    $output = [];
                }

                $_val = [];
            }

            if ($spaces < $currSpaces) {
                for ($i = 0; $i < ($currSpaces - $spaces) / 2; $i++) {
                    array_pop($keys);
                }
            }

            if (is_array($_val)) {
                $_key = rtrim($_key, ':');
                $keys[] = $_key;

                if ($_key == 'Histogram' || $_key == 'Colormap') {
                    $raw = $_key;
                }
            }

            $currSpaces = $spaces;
            $arr = &$output;

            foreach ($keys as $key) {
                if (!isset($arr[$key])) {
                    $arr[$key] = $_val;
                }

                $arr = &$arr[$key];
            }

            if (!is_array($_val)) {
                $arr[$_key] = $_val;
            }

        }

        $outputs[] = $output['Image'];

        return count($outputs) > 1 ? $outputs : $outputs[0];
    }
}
