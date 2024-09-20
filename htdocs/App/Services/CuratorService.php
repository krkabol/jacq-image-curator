<?php declare(strict_types=1);

namespace App\Services;

use App\Model\Database\Entity\Herbaria;
use App\Model\Database\Entity\Photos;
use App\Model\Database\Entity\PhotosStatus;
use App\Model\Database\EntityManager;
use App\Model\FileManagement\FileInsideCuratorBucket;
use App\Model\ImportStages\StageFactory;
use Doctrine\Common\Collections\Criteria;
use League\Pipeline\Pipeline;
use Nette\Security\AuthenticationException;

readonly class CuratorService
{


    public function __construct(protected readonly EntityManager $entityManager, protected readonly S3Service $s3Service, protected readonly StageFactory $stageFactory, protected readonly StorageConfiguration $storageConfiguration)
    {
    }

    public function getAllStatuses(): array
    {
        return $this->entityManager->getPhotosStatusRepository()->findBy([], ['id' => 'ASC']);
    }

    /**
     * On curator request read curatorBucket and insert files basic info into the database
     */
    public function registerNewFiles(Herbaria $herbarium): void
    {
        /** @var FileInsideCuratorBucket $file */
        foreach ($this->getEligibleCuratorBucketFiles($herbarium) as $file) {
            $entity = new Photos();
            $entity
                ->setCreatedAt()
                ->setLastEditAt()
                ->setOriginalFilename($file->name)
                ->setStatus($this->entityManager->getReference(PhotosStatus::class, PhotosStatus::WAITING))
                ->setHerbarium($herbarium)
                ->setArchiveFileSize($file->size);
            $this->entityManager->persist($entity);
        }
        $this->entityManager->flush();
    }

    protected function getEligibleCuratorBucketFiles(Herbaria $herbarium): array
    {
        return array_filter($this->getAllCuratorBucketFiles($herbarium), function ($item) {
            return $item->isEligibleToBeImported() === true;
        });
    }

    public function getAllCuratorBucketFiles(Herbaria $herbarium): array
    {
        $files = [];

        foreach ($this->s3Service->listObjects($herbarium->getBucket()) as $filename) {
            /** @var Photos $entity */
            $entity = $this->entityManager->getPhotosRepository()->findOneBy(["status" => [PhotosStatus::WAITING, PhotosStatus::CONTROL_ERROR], "herbarium" => $herbarium, "originalFilename" => $filename["Key"]]);
            if ($entity === NULL) {
                $file = new FileInsideCuratorBucket($filename["Key"], (int)$filename["Size"], $filename["LastModified"], false, false, NULL, NULL);
            } else {
                $alreadyWaiting = $entity->getStatus()->getId() === PhotosStatus::WAITING;
                $hasControlError = $entity->getStatus()->getId() === PhotosStatus::CONTROL_ERROR;
                $file = new FileInsideCuratorBucket($filename["Key"], (int)$filename["Size"], $filename["LastModified"], $alreadyWaiting, $hasControlError, $entity->getId(), $entity->getMessage());
            }
            $files[] = $file;
        }
        return $files;
    }

    public function importNewFiles(): Pipeline
    {
        return (new Pipeline())
            ->pipe($this->stageFactory->createDownloadStage())
            ->pipe($this->stageFactory->createThumbnailStage())
            ->pipe($this->stageFactory->createBarcodeStage())
            ->pipe($this->stageFactory->createDuplicityStage())
            ->pipe($this->stageFactory->createConvertStage())
            ->pipe($this->stageFactory->createTransferStage());
    }

    public function getOrphanedItems(Herbaria $herbarium): array
    {
        $files = [];
        $dbItems = $this->entityManager->getPhotosRepository()->findBy(["status" => [PhotosStatus::WAITING, PhotosStatus::CONTROL_ERROR], "herbarium" => $herbarium]);
        foreach ($dbItems as $photo) {
            /** @var Photos $photo */
            if (!$this->s3Service->objectExists($herbarium->getBucket(), $photo->getOriginalFilename())) {
                $files[] = $photo;
            }
        }
        return $files;
    }


    public function getLatestImports(Herbaria $herbarium): array
    {
        return $this->entityManager->getPhotosRepository()->findBy(["herbarium" => $herbarium, "status" => [3, 4, 5]], ["lastEdit" => Criteria::DESC], 30);

    }

    public function getPhotoWithError(Herbaria $herbarium, int $photoId): ?Photos
    {
        return $this->entityManager->getPhotosRepository()->findOneBy(["id" => $photoId, "herbarium" => $herbarium, "status" => $this->entityManager->getReference(PhotosStatus::class, PhotosStatus::CONTROL_ERROR)]);
    }

    public function deleteNotImportedPhoto(Herbaria $herbarium, Photos $photo): void
    {
        if ($herbarium->getId() === $photo->getHerbarium()->getId()) {
            $this->entityManager->remove($photo);
            $this->entityManager->flush();
            $this->s3Service->deleteObject($this->storageConfiguration->getCuratorBucket(), $photo->getOriginalFilename());
            return;
        }
        throw new AuthenticationException("Not allowed to delete photo.");

    }

    public function reimportPhoto(Herbaria $herbarium, Photos $photo, ?string $manualSpecimenId = NULL): void
    {

        if ($herbarium->getId() === $photo->getHerbarium()->getId()) {
            $photo
                ->setLastEditAt()
                ->setMessage(NULL)
                ->setSpecimenId($manualSpecimenId)
                ->setStatus($this->entityManager->getReference(PhotosStatus::class, PhotosStatus::WAITING));
            $this->entityManager->flush();
            return;
        }
        throw new AuthenticationException("Not allowed to delete photo.");

    }

}
