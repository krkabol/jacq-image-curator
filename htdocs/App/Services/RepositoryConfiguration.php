<?php declare(strict_types = 1);

namespace App\Services;

use App\Exceptions\ConfigurationException;
use App\Model\Database\Entity\Photos;

final readonly class RepositoryConfiguration
{

    public const string TEMP_FILE = 'default';
    public const string TEMP_ZBAR_FILE = 'default_zbar';
    public const string TEMP_DUPLICATE_FILE = 'duplicate';

    /**
     * @param mixed[] $config
     */
    public function __construct(protected array $config, protected TempDir $tempDir)
    {
    }

    public function getArchiveBucket(): string
    {
        return $this->getKey('archiveBucket', 'Archive bucket not set.');
    }

    public function getImageServerBucket(): string
    {
        return $this->getKey('jp2Bucket', 'Image server bucket not set.');
    }

    public function getJp2Quality(): int
    {
        return $this->getKey('jp2Quality', 'Compression for image server files not set.');
    }

    /**
     * used only for migrations, where tif Archive Master already exists
     */
    public function getPhotoNameRegex(): string
    {
        return $this->config['photoRegex'];
    }

    public function getBarcodeRegex(): string
    {
        return $this->getKey('barcodeRegex');
    }

    public function getRegexSpecimenPartName(): string
    {
        return $this->getKey('regexSpecimenPartName');
    }

    public function getRegexHerbariumPartName(): string
    {
        return $this->getKey('regexHerbariumPartName');
    }

    public function getImageServerInfoUrl(string $jp2ObjectName): string
    {
        return $this->getImageServerBaseUrl() . $jp2ObjectName;
    }

    public function getZbarImageSize(): int
    {
        return $this->getKey('zbarImageHeight');
    }

    public function getThumbnailSize(): int
    {
        return $this->getKey('thumbImageWidth');
    }

    public function getPreviewSize(): int
    {
        return $this->getKey('previewImageSize');
    }

    public function getPreviewQuality(): int
    {
        return $this->getKey('previewQuality');
    }

    public function getImageServerUrlThumbnail(string $jp2ObjectName): string
    {
        return $this->getImageServerBaseUrl() . $jp2ObjectName . '/full/' . $this->getThumbnailSize() . ',/0/default.jpg';
    }

    public function getSpecimenNameRegex(): string
    {
        return $this->getKey('specimenRegex');
    }

    public function getImportTempPath(Photos $photo): string
    {
        return $this->tempDir->getPath(self::TEMP_FILE . '.' . pathinfo($photo->getOriginalFilename(), PATHINFO_EXTENSION));
    }

    public function getImportTempJp2Path(): string
    {
        return $this->tempDir->getPath(self::TEMP_FILE . '.jp2');
    }

    public function getImportTempZbarPath(): string
    {
        return $this->tempDir->getPath(self::TEMP_ZBAR_FILE . '.png');
    }

    public function getImportTempDuplicatePath(Photos $photo): string
    {
        return $this->tempDir->getPath(self::TEMP_DUPLICATE_FILE . '.'.pathinfo($photo->getArchiveFilename(), PATHINFO_EXTENSION));
    }

    public function createS3Jp2Name(Photos $photo): string
    {
        return $photo->getFullSpecimenId() . '_' . $photo->getId() . '.jp2';
    }

    public function createS3TifName(Photos $photo): string
    {
        return $photo->getFullSpecimenId() . '_' . $photo->getId() . '.tif';
    }

    protected function getKey(string $key, string $msg = ''): mixed
    {
        if (!isset($this->config[$key])) {
            $text = $msg === '' ? 'Configuration parameter ' . strtoupper($key) . ' not set!' : $msg;

            throw new ConfigurationException($text);
        }

        return $this->config[$key];
    }

    protected function getImageServerBaseUrl(): string
    {
        return $this->getKey('imageServerBaseUrl');
    }

}
