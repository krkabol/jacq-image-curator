<?php

declare(strict_types=1);

namespace app\Model;

use app\Services\S3Service;
use app\Services\StorageConfiguration;
use app\Services\TempDir;
use Imagick;


class PhotoOfSpecimen
{

    protected string $sourceBucket;
    protected string $objectKey;
    protected S3Service $s3Service;
    protected TempDir $tempDir;

    protected StorageConfiguration $storageConfiguration;

    protected bool $isDownloaded = false;
    protected ?Imagick $imagick = null;

    protected int $height;
    protected int $width;
    protected int $jp2Size;
    protected int $tiffSize;
    protected string $herbariumAcronym;
    protected string $specimenId;


    public function __construct(string $bucket, string $objectKey, S3Service $s3Service, TempDir $tempDir, StorageConfiguration $storageConfiguration)
    {
        $this->sourceBucket = $bucket;
        $this->objectKey = $objectKey;
        $this->s3Service = $s3Service;
        $this->tempDir = $tempDir;
        $this->storageConfiguration = $storageConfiguration;
    }

    public function getImagick(): Imagick
    {
        if ($this->imagick === null) {
            $this->imagick = new Imagick($this->getTempfile());
        }

        return $this->imagick;
    }

    public function getTempfile()
    {
        $this->downloadFromS3();
        return $this->getTempfileName();
    }

    protected function downloadFromS3(): PhotoOfSpecimen
    {
        if (!$this->isDownloaded) {
            $this->s3Service->getObject($this->sourceBucket, $this->objectKey, $this->getTempfileName());
            $this->isDownloaded = true;
        }
        return $this;
    }

    protected function getTempfileName()
    {
        return $this->tempDir->getPath($this->getObjectKey());
    }

    public function getObjectKey(): string
    {
        return $this->objectKey;
    }

    public function getJP2Fullname(): string
    {
        return $this->tempDir->getPath($this->storageConfiguration->getJP2ObjectKey($this->getObjectKey()));
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function setHeight(int $height): PhotoOfSpecimen
    {
        $this->height = $height;
        return $this;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function setWidth(int $width): PhotoOfSpecimen
    {
        $this->width = $width;
        return $this;
    }


    public function getHerbariumAcronym(): string
    {
        return $this->herbariumAcronym;
    }

    public function setHerbariumAcronym(string $acronym): PhotoOfSpecimen
    {
        $this->herbariumAcronym = $acronym;
        return $this;
    }

    public function getSpecimenId(): string
    {
        return $this->specimenId;
    }

    public function setSpecimenId(string $id): PhotoOfSpecimen
    {
        $this->specimenId = $id;
        return $this;
    }

    public function unsetImagick()
    {
        unset($this->imagick);
        return $this;
    }

    public function getJp2Size(): int
    {
        return $this->jp2Size;
    }

    public function setJp2Size(int $jp2Size): PhotoOfSpecimen
    {
        $this->jp2Size = $jp2Size;
        return $this;
    }

    public function getTiffSize(): int
    {
        return $this->tiffSize;
    }

    public function setTiffSize(int $tiffSize): PhotoOfSpecimen
    {
        $this->tiffSize = $tiffSize;
        return $this;
    }


}
