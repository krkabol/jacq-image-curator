<?php declare(strict_types=1);

namespace App\Services;

use Imagick;

class ImageService
{

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

}
