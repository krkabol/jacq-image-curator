<?php

declare(strict_types=1);

namespace app\Model\ImportStages;

use App\Model\Database\EntityManager;
use app\Services\S3Service;
use app\Services\StorageConfiguration;
use app\Services\TempDir;

class StageFactory
{

    public function __construct(protected readonly S3Service $s3Service,protected readonly  TempDir $tempDir,protected readonly  EntityManager $entityManager, protected readonly StorageConfiguration $storageConfiguration)
    {
    }

    public function createDownloadControlStage(): DownloadStage
    {
        return new DownloadStage($this->s3Service, $this->storageConfiguration);
    }
//
//    public function createFilenameControlStage(): FilenameControlStage
//    {
//        $result = $this->entityManager->createQuery("SELECT a.acronym FROM app\Model\Database\Entity\Herbaria a")->getScalarResult();
//        $herbariaAvailable = array_column($result, "acronym");
//        return new FilenameControlStage($herbariaAvailable);
//    }
}
