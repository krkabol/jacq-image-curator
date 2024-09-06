<?php declare(strict_types=1);

namespace App\Services;

use app\Model\Database\Entity\Herbaria;
use app\Model\Database\Entity\Photos;
use app\Model\Database\Entity\PhotosStatus;
use App\Model\Database\EntityManager;
use App\Model\FileManagement\FileInsideCuratorBucket;
use app\Model\ImportStages\StageFactory;
use League\Pipeline\Pipeline;

readonly class CuratorService
{


    public function __construct(protected readonly EntityManager $entityManager, protected readonly S3Service $s3Service, protected readonly StageFactory $stageFactory)
    {
    }

    public function getAllStatuses(): array
    {
        return $this->entityManager->getPhotosStatusRepository()->findAll();
    }

    /**
     * On curator request read curatorBucket and insert files basic info into the database
     */
    public function registerNewFiles($herbariumId)
    {
        /** @var FileInsideCuratorBucket $file */
        foreach ($this->getEligibleCuratorBucketFiles($herbariumId) as $file) {
            $entity = new Photos();
            $entity
                ->setCreatedAt()
                ->setLastEditAt()
                ->setOriginalFilename($file->name)
                ->setStatus($this->entityManager->getReference(PhotosStatus::class, PhotosStatus::WAITING))
                ->setHerbarium($this->entityManager->getReference(Herbaria::class, $herbariumId))
                ->setArchiveFileSize($file->size);
            $this->entityManager->persist($entity);
        }
        $this->entityManager->flush();
    }

    public function getImportPipeline(): Pipeline
    {
        return (new Pipeline())
            ->pipe($this->stageFactory->createDownloadControlStage());
//            ->pipe($this->stageFactory->createFilenameControlStage())
//            ->pipe($this->stageFactory->createDimensionsStage())
//            ->pipe($this->stageFactory->createBarcodeStage())
//            ->pipe($this->stageFactory->createUpdateRecordStage());
    }

    protected function getEligibleCuratorBucketFiles($herbariumId): array
    {
        return array_filter($this->getAllCuratorBucketFiles($herbariumId), function ($item) {
            return $item->isEligibleToBeImported() === true;
        });
    }

    public function getAllCuratorBucketFiles($herbariumId): array
    {
        $herbarium = $this->entityManager->getHerbariaRepository()->find($herbariumId);
        $files = [];

        foreach ($this->s3Service->listObjects($herbarium->getBucket()) as $filename) {
            $alreadyWaiting = !(($this->entityManager->getPhotosRepository()->findOneBy(["status" => PhotosStatus::WAITING, "herbarium" => $herbarium, "originalFilename" => $filename["Key"]]) === NULL));
            $file = new FileInsideCuratorBucket($filename["Key"], (int) $filename["Size"] , $filename["LastModified"], $alreadyWaiting);
            $files[] = $file;

        }
        return $files;
    }


}
