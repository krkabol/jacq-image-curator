<?php

declare(strict_types=1);

namespace app\Model\UpdateStages;

use app\Model\Database\Entity\Photos;
use app\Services\StorageConfiguration;
use app\Services\TempDir;
use Exception;
use GuzzleHttp\Client;
use Imagick;
use League\Pipeline\StageInterface;


class BarcodeStageException extends BaseStageException
{

}

//TODO duplicate code in all Update/Import Stages, move code into services
class BarcodeStage implements StageInterface
{
    protected TempDir $tempDir;

    protected StorageConfiguration $configuration;
    protected Client $client;
    protected Photos $item;

    public function __construct(TempDir $tempDir, StorageConfiguration $configuration, Client $client)
    {
        $this->tempDir = $tempDir;
        $this->configuration = $configuration;
        $this->client = $client;
    }

    public function __invoke($payload)
    {
        try {
            $this->item = $payload;
            $this->downloadFromIIIF();
            $this->createContrastedImage();
            $this->validateFilename();
            unlink($this->getContrastTempFileName());
            unlink($this->getDownloadedTempFile());
            return $this->item;
        } catch (BarcodeStageException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new BarcodeStageException('problem with barcode processing: ' . $e->getMessage());
        }
    }

    protected function downloadFromIIIF()
    {
        $response = $this->client->request('GET', $this->configuration->getImageIIIFURL4Barcode($this->item->getJp2Filename()), ['stream' => true]);
        $statusCode = $response->getStatusCode();
        if ($statusCode == 404) {
            throw new BarcodeStageException('Error 404 - unable download image');
        }
        $file = fopen($this->getDownloadedTempFile(), 'wb');
        if (!$file) {
            throw new BarcodeStageException('Can not write temp file.');
        }

        $body = $response->getBody();
        while (!$body->eof()) {
            fwrite($file, $body->read(1024));
        }
        fclose($file);

    }

    protected function getDownloadedTempFile(): string
    {
        return $this->tempDir->getPath($this->item->getJp2Filename());
    }

    protected function createContrastedImage(): void
    {
        $imagick = new Imagick($this->getDownloadedTempFile());
        $imagick->modulateImage(100, 0, 100);
        $imagick->whiteThresholdImage('#a9a9a9');
        $imagick->contrastImage(true);
        $imagick->setImageFormat('jpg');
        $imagick->writeImage($this->getContrastTempFileName());
        $imagick->destroy();
        $imagick->clear();
    }

    protected function getContrastTempFileName(): string
    {
        return $this->getDownloadedTempFile() . "barcode.jpg";
    }

    protected function validateFilename(): void
    {
        $isValid = false;
        $codes = $this->detectCodes();
        foreach ($codes as $code) {
            $parts = [];
            if (preg_match($this->configuration->getBarcodeRegex(), $code, $parts)) {
                if ($this->item->getHerbarium()->getAcronym() === strtoupper($parts['herbarium']) &&
                    (int)$this->item->getSpecimenId() === (int)$parts['specimenId']) {
                    $isValid = true;
                }
            }
        }
        if (!$isValid) {
            throw new BarcodeStageException("wrong barcode or image name" . ". Detected code(s): " . implode($codes));
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
