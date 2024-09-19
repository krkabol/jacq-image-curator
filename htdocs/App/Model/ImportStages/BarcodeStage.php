<?php

declare(strict_types=1);

namespace App\Model\ImportStages;

use App\Model\Database\Entity\Photos;
use App\Services\ImageService;
use App\Services\StorageConfiguration;
use Exception;
use Imagick;
use League\Pipeline\StageInterface;

class BarcodeStageException extends ImportStageException
{

}

class BarcodeStage implements StageInterface
{
    protected Photos $item;
    protected array $barcodes;

    public function __construct(protected readonly StorageConfiguration $storageConfiguration, protected readonly ImageService $imageService)
    {
    }

    public function __invoke($payload)
    {
        try {
            $this->item = $payload;
            $imagick = $this->imageService->createImagick($this->storageConfiguration->getImportTempPath($this->item));
            $this->readDimensions($imagick);
            /** skip detection when manually inserted */
            if ($this->item->getSpecimenId() === NULL) {
                $this->createContrastedImage($imagick);
                $this->detectCodes();
                $this->harvestCodes();
            }
            return $this->item;
        } catch (BarcodeStageException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new BarcodeStageException('problem with barcode processing: ' . $e->getMessage());
        }
    }

    //TODO isolate to stage? - requires double imagick creation..
    protected function readDimensions(Imagick $imagick): Imagick
    {
        $this->item->setWidth($imagick->getImageWidth());
        $this->item->setHeight($imagick->getImageHeight());
        return $imagick;
    }

    protected function createContrastedImage(Imagick $imagick): void
    {
        $imagick = $this->imageService->resizeImage($imagick, $this->storageConfiguration->getZbarImageSize());
        $imagick->modulateImage(100, 0, 100);
//        $imagick->adaptiveThresholdImage(150, 150, 1);
        $imagick->setImageFormat('png');
//        $imagick->setImageCompressionQuality(80);
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
            throw new BarcodeStageException("Invalid barcode. Detected non-valid code(s): " . implode($this->barcodes));
        }
    }

}
