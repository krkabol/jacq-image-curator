<?php declare(strict_types=1);

namespace App\Services;


use App\Model\Database\Entity\Photos;
use App\Model\MigrationStages\FilenameControlException;

final readonly class StorageConfiguration
{
    const string TEMP_FILE = "default";
    const string TEMP_ZBAR_FILE = "default_zbar";

    public function __construct(protected array $config, protected TempDir $tempDir)
    {
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
        return $this->getIIIFBaseUrl() . $jp2ObjectName . "/full/,".$this->getZbarImageSize()."/0/default.jpg";
    }

    public function getZbarImageSize(): int
    {
        return $this->config['zbarImageHeight'];
    }

    public function getImageIIIFURL4Thumbnail($jp2ObjectName): string
    {
        return $this->getIIIFBaseUrl() . $jp2ObjectName . "/full/".$this->config['thumbImageWidth'].",/0/default.jpg";
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

    public function getImportTempPath(Photos $photo): string
    {
        return $this->tempDir->getPath(self::TEMP_FILE . "." . pathinfo($photo->getOriginalFilename(), PATHINFO_EXTENSION));
    }
    public function getImportTempJP2Path(Photos $photo): string
    {
        return $this->tempDir->getPath(self::TEMP_FILE . ".jp2");
    }
    public function getImportTempZbarPath(Photos $photo): string
    {
        return $this->tempDir->getPath(self::TEMP_ZBAR_FILE . ".png");
    }

    public function createS3JP2Name(Photos $photo): string
    {
        return $photo->getFullSpecimenId()."_".$photo->getId().".jp2";
    }
    public function createS3TIFName(Photos $photo): string
    {
        return $photo->getFullSpecimenId()."_".$photo->getId().".tif";
    }
}
