<?php declare(strict_types=1);

namespace app\Services;

use app\Model\ImportStages\FilenameControlException;

final class StorageConfiguration
{

    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function getAllBuckets(): array
    {
        return [$this->getNewBucket(), $this->getArchiveBucket(), $this->getJP2Bucket()];
    }

    public function getNewBucket(): string
    {
        return $this->config['newBucket'];
    }

    public function getArchiveBucket(): string
    {
        return $this->config['archiveBucket'];
    }

    public function getJP2Bucket(): string
    {
        return $this->config['jp2Bucket'];
    }

    public function getJP2Quality(): int
    {
        return $this->config['jp2Quality'];
    }

    public function getPhotoNameRegex(): string
    {
        return $this->config['photoRegex'];
    }

    public function getBarcodeRegex(): string
    {
        return $this->config['barcodeRegex'];
    }

    public function getImageIIIFInfoURL($jp2ObjectName): string
    {
        return $this->getIIIFBaseUrl() . $jp2ObjectName;
    }

    public function getImageIIIFURL4Barcode($jp2ObjectName): string
    {
        return $this->getIIIFBaseUrl() . $jp2ObjectName . "/full/,".$this->config['zbarImageHeight']."/0/default.jpg";
    }

    public function getImageIIIFURL4Thumbnail($jp2ObjectName): string
    {
        return $this->getIIIFBaseUrl() . $jp2ObjectName . "/full/".$this->config['thumbImageWidth'].",/0/default.jpg";
    }

    public function getZbarThreshold()
    {
        return $this->config["zbarThreshold"];
    }

    protected function getIIIFBaseUrl(): string
    {
        return $this->config['iiif'];
    }

    public function getJP2ObjectKey($archiveObjectKey): string
    {
        return str_replace("tif", "jp2", $archiveObjectKey);
    }

    public function getHerbariumAcronymFromId($specimenId): string
    {
        return strtoupper($this->splitId($specimenId)["herbarium"]);
    }

    protected function splitId($specimenId)
    {
        $parts = [];
        if (preg_match($this->getSpecimenNameRegex(), $specimenId, $parts)) {
            return $parts;
        } else {
            throw new FilenameControlException("invalid name format: " . $specimenId);
        }
    }

    public function getSpecimenNameRegex(): string
    {
        return $this->config['specimenRegex'];
    }

    public function getSpecimenIdFromId($specimenId): int
    {
        return (int) $this->splitId($specimenId)["specimenId"];
    }

}
