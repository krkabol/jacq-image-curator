<?php declare(strict_types = 1);

namespace App\Model\ImportStages;

use App\Model\Database\Entity\Photos;
use App\Model\ImportStages\Exceptions\BarcodeStageException;
use App\Services\ImageService;
use App\Services\RepositoryConfiguration;
use Imagick;
use League\Pipeline\StageInterface;
use Throwable;

class BarcodeStage implements StageInterface
{

    protected Photos $item;

    /** @var string [] */
    protected array $barcodes;

    public function __construct(protected readonly RepositoryConfiguration $repositoryConfiguration, protected readonly ImageService $imageService)
    {
    }

    protected function createContrastedImage(Imagick $imagick): void
    {
        $imagick = $this->imageService->resizeImage($imagick, $this->repositoryConfiguration->getZbarImageSize());
        $imagick->modulateImage(100, 0, 100);
        // adaptive threshold had worse results than unmodified image        * $imagick->adaptiveThresholdImage(150, 150, 1);
        $imagick->setImageFormat('png');
        $imagick->writeImage($this->repositoryConfiguration->getImportTempZbarPath($this->item));
        $imagick->destroy();
        unset($imagick);
    }

    /**
     * use Zbar to detect Barcodes
     *
     * @link https://manpages.ubuntu.com/manpages/jammy/man1/zbarimg.1.html
     */
    protected function detectCodes(): void
    {
        $output = [];
        $returnVar = 0;
        $info = exec('zbarimg --quiet --raw ' . escapeshellarg($this->repositoryConfiguration->getImportTempZbarPath($this->item)), $output, $returnVar);

        switch ($returnVar) {
            case 1:
            case 2:
                throw new BarcodeStageException('zbar script error: ' . $info);
            case 4:
                throw new BarcodeStageException('No barcode detected');
        }

        $this->barcodes = $output;
    }

    protected function harvestCodes(): void
    {
        $isValid = false;
        foreach ($this->barcodes as $code) {
            $parts = [];
            if (preg_match($this->repositoryConfiguration->getBarcodeRegex(), $code, $parts)) {
                if ($this->item->getHerbarium()->getAcronym() === strtoupper($parts['herbarium']) && $parts['specimenId'] !== '') {
                    $isValid = true;
                    $this->item->setSpecimenId($parts['specimenId']);
                }
            }
        }

        if (!$isValid) {
            throw new BarcodeStageException('Invalid barcode. Detected code(s): ' . implode($this->barcodes));
        }
    }

    public function __invoke(mixed $payload): mixed
    {
        try {
            $this->item = $payload;
            /**
             * skip detection when manually inserted id
             */
            if ($this->item->getSpecimenId() === null) {
                $imagick = $this->imageService->createImagick($this->repositoryConfiguration->getImportTempPath($this->item));
                $this->createContrastedImage($imagick);
                $this->detectCodes();
                $this->harvestCodes();
            }

            return $this->item;
        } catch (BarcodeStageException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new BarcodeStageException('problem with barcode processing: ' . $e->getMessage());
        }
    }

}
