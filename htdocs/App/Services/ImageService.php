<?php declare(strict_types=1);

namespace App\Services;

use Imagick;

class ImageService
{

    public const string ENCODING = "UTF-8";

    public function __construct(protected readonly S3Service $S3Service, protected readonly RepositoryConfiguration $storageConfiguration)
    {
    }

    /**
     * in case the image is "mulipage", like a TIF containing a thumb,
     * this helps to find the largest
     * and returns index that Imagick need to be set to.
     */
    public function getLargestImageIndex(Imagick $imagick): int
    {
        $numberOfImages = $imagick->getNumberImages();
        $maxWidth = 0;
        $maxHeight = 0;
        $largestImageIndex = null;
        for ($i = 0; $i < $numberOfImages; $i++) {
            $imagick->setIteratorIndex($i);
            $width = $imagick->getImageWidth();
            $height = $imagick->getImageHeight();

            if ($width * $height > $maxWidth * $maxHeight) {
                $maxWidth = $width;
                $maxHeight = $height;
                $largestImageIndex = $i;
            }
        }

        return $largestImageIndex;
    }

    /**
     * creates Imagick instance with the largest page of file activated
     */
    public function createImagick(string $path): Imagick
    {
        $imagick = new Imagick($path);
        $imagick->setIteratorIndex($this->getLargestImageIndex($imagick));

        return $imagick;
    }

    public function resizeImage(Imagick $imagick, int $maxEdgeLength): Imagick
    {
        $width = $imagick->getImageWidth();
        $height = $imagick->getImageHeight();
        if ($width > $maxEdgeLength || $height > $maxEdgeLength) {
            if ($width > $height) {
                $newWidth = $maxEdgeLength;
                $newHeight = intval(($maxEdgeLength / $width) * $height);
            } else {
                $newHeight = $maxEdgeLength;
                $newWidth = intval(($maxEdgeLength / $height) * $width);
            }

            $imagick->resizeImage($newWidth, $newHeight, Imagick::FILTER_GAUSSIAN, 1);
        }

        return $imagick;
    }

    protected function convertArrayEncoding(array $input, string $to_encoding, string $from_encoding): mixed
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

    protected function recursiveArrayToString(array $array): string
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
    protected function parseIdentify(string $info): mixed
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

    public function readIdentify(Imagick $imagick): array
    {
        $identify = $imagick->identifyImage(true);
        $identify['rawOutput'] = $this->parseIdentify($identify['rawOutput']);
        $encoding = mb_detect_encoding($this->recursiveArrayToString($identify));
        return $this->convertArrayEncoding($identify, self::ENCODING, $encoding);

    }

    public function readExif(string $path): array
    {
        $exifData = exif_read_data($path);
        if ($exifData !== false) {
            $encoding = mb_detect_encoding($this->recursiveArrayToString($exifData));
            return $this->convertArrayEncoding($exifData, self::ENCODING, $encoding);

        }
        return [];
    }
}
