<?php

declare(strict_types=1);

namespace app\Model\ImportStages;

use app\Model\Database\Entity\Photos;
use app\Services\StorageConfiguration;
use League\Pipeline\StageInterface;

class BarcodeStageException extends ImportStageException
{

}

class BarcodeStage implements StageInterface
{
    protected Photos $item;
    protected array $barcodes;

    public function __construct(protected readonly StorageConfiguration $storageConfiguration)
    {
    }

    public function __invoke($payload)
    {
        try {
            $this->item = $payload;
            $imagick = $this->readDimensions();
            $this->createContrastedImage($imagick);
            $this->detectCodes();
            $this->harvestCodes();
            return $this->item;
        } catch (BarcodeStageException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new BarcodeStageException('problem with barcode processing: ' . $e->getMessage());
        }
    }

    protected function readDimensions(): \Imagick
    {
        $imagick = new \Imagick($this->storageConfiguration->getImportTempPath($this->item));
        $this->item->setWidth($imagick->getImageWidth());
        $this->item->setHeight($imagick->getImageHeight());
        return $imagick;
    }

    protected function createContrastedImage(\Imagick $imagick): void
    {
        $imagick->modulateImage(100, 0, 100);
        $imagick->adaptiveThresholdImage(100, 100, 1);
        $imagick->setImageFormat('jpg');
        $width = $this->item->getWidth();
        $height = $this->item->getHeight();
        if ($width > 1500 || $height > 1500) {
            if ($width > $height) {
                $newWidth = $this->storageConfiguration->getZbarImageSize();
                $newHeight = intval(($this->storageConfiguration->getZbarImageSize() / $width) * $height);
            } else {
                $newHeight = $this->storageConfiguration->getZbarImageSize();
                $newWidth = intval(($this->storageConfiguration->getZbarImageSize() / $height) * $width);
            }
            $imagick->resizeImage($newWidth, $newHeight, \Imagick::FILTER_GAUSSIAN, 1);
        }
        $imagick->setImageCompressionQuality(80);
        $imagick->setImageFormat('jpg');
        $imagick->writeImage($this->storageConfiguration->getImportTempZbarPath($this->item));
        $imagick->destroy();
        $imagick->clear();
        unset($imagick);
    }

    /**
     * use Zbar to detect Barcodes
     * @link https://manpages.ubuntu.com/manpages/jammy/man1/zbarimg.1.html
     */
    protected function detectCodes(): void
    {
        $output = [];
        $returnVar = 0;
        $info = exec("zbarimg --quiet --raw " . escapeshellarg($this->storageConfiguration->getImportTempZbarPath($this->item)), $output, $returnVar);

        switch ($returnVar) {
            case 1:
            case 2:
                throw new BarcodeStageException("zbar script error: " . $info);
            case 4:
                throw new BarcodeStageException("No barcode was detected");
        }
        $this->barcodes = $output;
    }

    protected function harvestCodes(): void
    {
        $isValid = false;
        foreach ($this->barcodes as $code) {
            $parts = [];
            if (preg_match($this->storageConfiguration->getBarcodeRegex(), $code, $parts)) {
                if ($this->item->getHerbarium()->getAcronym() === strtoupper($parts['herbarium']) && $parts['specimenId'] != "") {
                    $isValid = true;
                    $this->item->setSpecimenId($parts['specimenId']);
                }
            }
        }
        if (!$isValid) {
            throw new BarcodeStageException("barcode error: " . ". Detected code(s): " . implode($this->barcodes));
        }
    }

}
