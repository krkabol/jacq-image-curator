<?php declare(strict_types=1);

namespace app\Services;

use app\Model\Database\Entity\Herbaria;
use app\Model\Stages\FilenameControlException;

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

    public function getSpecimenNameRegex(): string
    {
        return $this->config['regex'];
    }

    public function getJP2ObjectKey($archiveObjectKey): string
    {
        return str_replace("tif", "jp2", $archiveObjectKey);
    }

    public function getHerbariumAcronymFromId($specimenId): string
    {
        return $this->splitId($specimenId)["herbarium"];
    }

    public function getSpecimenIdFromId($specimenId): string
    {
        return $this->splitId($specimenId)["specimenId"];
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

}
