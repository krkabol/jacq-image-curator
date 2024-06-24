<?php

declare(strict_types=1);

namespace app\Model\Stages;

use App\Model\Database\EntityManager;
use app\Services\S3Service;
use app\Services\StorageConfiguration;
use app\Services\TempDir;

class StageFactory
{

    protected S3Service $s3Service;
    protected TempDir $tempDir;
    protected EntityManager $entityManager;
    protected StorageConfiguration $storageConfiguration;


    public function __construct(S3Service $s3Service, TempDir $tempDir, EntityManager $entityManager, StorageConfiguration $storageConfiguration)
    {
        $this->s3Service = $s3Service;
        $this->tempDir = $tempDir;
        $this->entityManager = $entityManager;
        $this->storageConfiguration = $storageConfiguration;
    }

    public function createNoveltyControlStage(): NoveltyControlStage
    {
        return new NoveltyControlStage($this->s3Service, $this->storageConfiguration);
    }

    public function createNotInDatabaseStage(): NotInDatabaseStage
    {
        return new NotInDatabaseStage($this->entityManager);
    }

    public function createRegisterStage(): RegisterStage
    {
        return new RegisterStage($this->entityManager, $this->storageConfiguration, $this->s3Service);
    }

    public function createCleanupStage(): CleanupStage
    {
        return new CleanupStage($this->s3Service, $this->storageConfiguration);
    }

    public function createArchiveStage(): ArchiveStage
    {
        return new ArchiveStage($this->s3Service, $this->storageConfiguration);
    }

    public function createConvertStage(): ConvertStage
    {
        return new ConvertStage($this->s3Service, $this->storageConfiguration);
    }

    public function createDimensionsStage(): DimensionsStage
    {
        return new DimensionsStage($this->s3Service, $this->storageConfiguration);
    }
    public function createFilenameControlStage(): FilenameControlStage
    {
        $result = $this->entityManager->createQuery("SELECT a.acronym FROM app\Model\Database\Entity\Herbaria a")->getScalarResult();
        $herbariaAvailable = array_column($result, "acronym");
        return new FilenameControlStage($herbariaAvailable);
    }
}
