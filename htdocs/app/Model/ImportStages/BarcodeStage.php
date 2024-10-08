<?php

declare(strict_types=1);

namespace app\Model\ImportStages;

use app\Model\PhotoOfSpecimen;
use League\Pipeline\StageInterface;

class BarcodeStageException extends BaseStageException
{

}

class BarcodeStage implements StageInterface
{
    const BARCODE_TEMPLATE = '/^(?P<herbarium>[a-zA-Z]+)[ _-]+(?P<specimenId>\d+)$/';
    const ZBAR_DIMENSION = 3000;
    protected PhotoOfSpecimen $item;

    public function __invoke($payload)
    {
        $this->item = $payload;
        $this->createContrastedImage();
        $this->validateFilename();
        unlink($this->getContrastTempFileName());
        return $this->item;
    }

    protected function createContrastedImage(): void
    {
        try {
            $imagick = new \Imagick($this->item->getTempfile());
            $imagick->modulateImage(100, 0, 100);
            $imagick->whiteThresholdImage('#a9a9a9');
            $imagick->contrastImage(true);
            $width = $imagick->getImageWidth();
            $height = $imagick->getImageHeight();

            if ($width > $height) {
                $newWidth = self::ZBAR_DIMENSION;
                $newHeight = intval((self::ZBAR_DIMENSION / $width) * $height);
            } else {
                $newHeight = self::ZBAR_DIMENSION;
                $newWidth = intval((self::ZBAR_DIMENSION / $height) * $width);
            }

            $imagick->resizeImage($newWidth, $newHeight, Imagick::FILTER_GAUSSIAN, 1);
            $imagick->setImageCompressionQuality(80);
            $imagick->setImageFormat('jpg');
            $imagick->writeImage($this->getContrastTempFileName());
            unset($imagick);
        } catch (\Exception $exception) {
            throw new BarcodeStageException($exception->getMessage());
        }
    }

    protected function getContrastTempFileName(): string
    {
        return $this->item->getTempfile() . "barcode";
    }

    protected function validateFilename(): void
    {
        $isValid = false;
        $codes = $this->detectCodes();
        foreach ($codes as $code) {
            $parts = [];
            if (preg_match(self::BARCODE_TEMPLATE, $code, $parts)) {
                if ($this->item->getHerbariumAcronym() === strtoupper($parts['herbarium']) &&
                    $this->item->getSpecimenId() === $parts['specimenId']) {
                    $isValid = true;
                }
            }
        }
        if (!$isValid) {
            throw new BarcodeStageException("wrong barcode or image name: " . $this->item->getObjectKey() . ". Detected code(s): " . implode($codes));
        }
    }

    protected function detectCodes(): array
    {
        $output = [];
        $returnVar = 0;
        $info = exec("zbarimg --quiet --raw " . escapeshellarg($this->getContrastTempFileName()), $output, $returnVar);

        if ($returnVar !== 0) {
            throw new BarcodeStageException("zbar script error: " . $info);
        }

        if (empty($output)) {
            throw new BarcodeStageException("empty output from zbar");
        }
        return $output;
    }

}
