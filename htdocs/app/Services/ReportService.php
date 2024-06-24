<?php

declare(strict_types=1);

namespace App\Services;

use app\Model\Database\Entity\Photos;
use App\Model\Database\EntityManager;

final class ReportService
{
    protected S3Service $S3Service;

    protected StorageConfiguration $storageConfiguration;
    protected $photosRepository;

    protected $dbRecords;

    public function __construct(S3Service $S3Service, StorageConfiguration $storageConfiguration, EntityManager $entityManager)
    {
        $this->S3Service = $S3Service;
        $this->storageConfiguration = $storageConfiguration;
        $this->photosRepository = $entityManager->getPhotosRepository();
    }

    public function dbRecordsMissingWithinArchive(): array
    {
        $missing = [];
        foreach ($this->getDbRecords() as $item) {
            if (!$this->S3Service->objectExists($this->storageConfiguration->getArchiveBucket(), $item->getArchiveFilename())) {
                $missing[] = $item;
            }
        }
        return $missing;
    }

    protected function getDbRecords()
    {
        if ($this->dbRecords === null) {
            $this->dbRecords = $this->photosRepository->findAll();
        }
        return $this->dbRecords;
    }

    public function dbRecordsMissingWithinIIIF(): array
    {
        $missing = [];
        foreach ($this->getDbRecords() as $item) {
            /** @var Photos $item */
            if (!$this->S3Service->objectExists($this->storageConfiguration->getJP2Bucket(), $this->storageConfiguration->getJP2ObjectKey($item->getArchiveFilename()))) {
                $missing[] = $item;
            }
        }
        return $missing;
    }

    public function unprocessedNewFiles(): array
    {
        return $this->S3Service->listObjects($this->storageConfiguration->getNewBucket());
    }

    public function TIFFsWithoutJP2(): array
    {
        $jp2s = $this->S3Service->listObjects($this->storageConfiguration->getJP2Bucket());
        return $this->findMissingObjects($this->getConvertedTiffsToJP2Names(), $jp2s);
    }

    protected function findMissingObjects($needle, $haystack)
    {
        $missingElements = array_filter($needle, function ($value) use ($haystack) {
            return !in_array($value, $haystack);
        });
        return $missingElements;
    }

    protected function getConvertedTiffsToJP2Names()
    {
        $tiffs = $this->S3Service->listObjects($this->storageConfiguration->getArchiveBucket());
        $mapper = $this->storageConfiguration;
        return array_map(function ($value) use ($mapper) {
            return $mapper->getJP2ObjectKey($value);
        }, $tiffs);
    }

    public function JP2sWithoutTIFF(): array
    {
        $jp2s = $this->S3Service->listObjects($this->storageConfiguration->getJP2Bucket());
        return $this->findMissingObjects($jp2s, $this->getConvertedTiffsToJP2Names());
    }

    public function TIFFsWithoutDbRecord(): array
    {
        $missing = [];
        $tiffs = $this->S3Service->listObjects($this->storageConfiguration->getArchiveBucket());
        foreach ($tiffs as $tiff) {
            if($this->photosRepository->findOneBy(["archiveFilename"=>$tiff]) === null){
                $missing[] = $tiff;
            }
        }
        return $missing;
    }
}
