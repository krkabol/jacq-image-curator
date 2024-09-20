<?php declare(strict_types = 1);

namespace App\Services;

use App\Model\Database\Entity\Photos;
use App\Model\Database\EntityManager;

final class ReportService
{

    protected $photosRepository;

    protected $dbRecords;

    public function __construct(protected readonly S3Service $S3Service, protected readonly StorageConfiguration $storageConfiguration, protected readonly EntityManager $entityManager)
    {
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

    public function TIFFsWithoutJP2(): array
    {
        $jp2s = $this->S3Service->listObjectsNamesOnly($this->storageConfiguration->getJP2Bucket());

        return $this->findMissingObjects($this->getConvertedTiffsToJP2Names(), $jp2s);
    }

    public function JP2sWithoutTIFF(): array
    {
        $jp2s = $this->S3Service->listObjectsNamesOnly($this->storageConfiguration->getJP2Bucket());

        return $this->findMissingObjects($jp2s, $this->getConvertedTiffsToJP2Names());
    }

    public function TIFFsWithoutDbRecord(): array
    {
        $missing = [];
        $tiffs = $this->S3Service->listObjectsNamesOnly($this->storageConfiguration->getArchiveBucket());
        foreach ($tiffs as $tiff) {
            if ($this->photosRepository->findOneBy(['archiveFilename' => $tiff]) === null) {
                $missing[] = $tiff;
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

    protected function findMissingObjects($needle, $haystack)
    {
        return array_filter($needle, fn ($value) => !in_array($value, $haystack));
    }

    protected function getConvertedTiffsToJP2Names()
    {
        $tiffs = $this->S3Service->listObjectsNamesOnly($this->storageConfiguration->getArchiveBucket());
        $mapper = $this->storageConfiguration;

        return array_map(fn ($value) => $mapper->getJP2ObjectKey($value), $tiffs);
    }

}
