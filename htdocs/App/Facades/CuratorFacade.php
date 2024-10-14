<?php declare(strict_types=1);

namespace App\Facades;

use App\Exceptions\SpecimenIdException;
use App\Model\Database\Entity\Herbaria;
use App\Model\Database\Entity\Photos;
use App\Model\Database\Entity\PhotosStatus;
use App\Model\Database\EntityManager;
use App\Model\FileManagement\FileInsideCuratorBucket;
use App\Model\ImportStages\StageFactory;
use App\Services\EntityServices\HerbariumService;
use App\Services\EntityServices\PhotoService;
use App\Services\RepositoryConfiguration;
use App\Services\S3Service;
use League\Pipeline\Pipeline;
use Nette\Security\AuthenticationException;

readonly class CuratorFacade
{

    public function __construct(protected EntityManager $entityManager, protected S3Service $s3Service, protected StageFactory $stageFactory, protected RepositoryConfiguration $repositoryConfiguration, protected PhotoService $photoService, protected HerbariumService $herbariumService)
    {
    }

    /**
     * @return PhotosStatus[]
     */
    public function getAllStatuses(): array
    {
        return $this->entityManager->getPhotosStatusRepository()->findBy([], ['id' => 'ASC']);
    }

    /**
     * On curator request read curatorBucket and insert files basic info into the database
     */
    public function registerNewFiles(): CuratorFacade
    {
        foreach ($this->getEligibleCuratorBucketFiles() as $file) {
            $entity = new Photos();
            $entity
                ->setCreatedAt()
                ->setLastEditAt()
                ->setOriginalFilename($file->name)
                ->setStatus($this->photoService->getWaitingStatus())
                ->setHerbarium($this->herbariumService->getCurrentUserHerbarium())
                ->setArchiveFileSize($file->size);
            $this->entityManager->persist($entity);
        }

        $this->entityManager->flush();
        return $this;
    }

    /**
     * @return FileInsideCuratorBucket[]
     */
    protected function getEligibleCuratorBucketFiles(): array
    {
        return array_filter($this->getAllCuratorBucketFiles(), fn($item) => $item->isEligibleToBeImported() === true);
    }

    /**
     * @return FileInsideCuratorBucket[]
     */
    public function getAllCuratorBucketFiles(): array
    {
        $files = [];
        foreach ($this->s3Service->listObjects($this->herbariumService->getCurrentUserHerbarium()->getBucket()) as $filename) {
            $entity = $this->photoService->findUnprocessedPhotoByOriginalFilename($filename['Key']);
            if ($entity === null) {
                $file = new FileInsideCuratorBucket($filename['Key'], (int)$filename['Size'], $filename['LastModified'], false, false, null, null);
            } else {
                $alreadyWaiting = $entity->getStatus()->getId() === PhotosStatus::WAITING;
                $hasControlError = $entity->getStatus()->getId() === PhotosStatus::CONTROL_ERROR;
                $file = new FileInsideCuratorBucket($filename['Key'], (int)$filename['Size'], $filename['LastModified'], $alreadyWaiting, $hasControlError, $entity->getId(), $entity->getMessage());
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
            ->pipe($this->stageFactory->createDimensionsStage())
            ->pipe($this->stageFactory->createBarcodeStage())
            ->pipe($this->stageFactory->createDuplicityStage())
            ->pipe($this->stageFactory->createConvertStage())
            ->pipe($this->stageFactory->createTransferStage());
    }

    /**
     * @return Photos[]
     */
    public function getOrphanedItems(): array
    {
        $photos = [];
        $dbItems = $this->entityManager->getPhotosRepository()->getOrphananble($this->herbariumService->getCurrentUserHerbarium());
        foreach ($dbItems as $photo) {
            if (!$this->s3Service->objectExists($this->herbariumService->getCurrentUserHerbarium()->getBucket(), $photo->getOriginalFilename())) {
                $photos[] = $photo;
            }
        }

        return $photos;
    }

    /**
     * @return Photos[]
     */
    public function getLatestImports(): array
    {
        return $this->photoService->findLastImported();
    }

    public function deleteNotImportedPhoto(Photos $photo): CuratorFacade
    {
        if ($this->herbariumService->getCurrentUserHerbarium() === $photo->getHerbarium()) {
            $this->s3Service->deleteObject($photo->getHerbarium()->getBucket(), $photo->getOriginalFilename());
            $this->entityManager->remove($photo);
            $this->entityManager->flush();

            return $this;
        }

        throw new AuthenticationException('Not allowed to delete photo.');
    }

    public function deleteJustFile(string $filename): CuratorFacade
    {
        $this->s3Service->deleteObject($this->herbariumService->getCurrentUserHerbarium()->getBucket(), $filename);
        return $this;
    }

    public function reimportPhoto(Photos $photo, ?string $manualSpecimenId = null): CuratorFacade
    {
        if ($this->herbariumService->getCurrentUserHerbarium() === $photo->getHerbarium()) {
            $photo
                ->setLastEditAt()
                ->setMessage(null)
                ->setSpecimenId($manualSpecimenId)
                ->setStatus($this->photoService->getWaitingStatus());
            $this->entityManager->flush();

            return $this;
        }

        throw new AuthenticationException('Not allowed to reimport photo.');
    }

    public function getHerbariumFromId(string $specimenId): Herbaria
    {
        $acronym = strtoupper($this->splitId($specimenId)[$this->repositoryConfiguration->getRegexHerbariumPartName()]);
        $herbarium = $this->herbariumService->findOneWithAcronym($acronym);
        if ($herbarium === NULL) {
            throw new SpecimenIdException("Unknown herbarium");
        }
        return $herbarium;
    }

    /**
     * @return string[]
     */
    protected function splitId($specimenId): array
    {
        $parts = [];
        if (preg_match($this->repositoryConfiguration->getSpecimenNameRegex(), $specimenId, $parts)) {
            return $parts;
        } else {
            throw new SpecimenIdException('invalid name format: ' . $specimenId);
        }
    }

    public function getSpecimenIdFromId(string $specimenId): int
    {
        return (int)$this->splitId($specimenId)[$this->repositoryConfiguration->getRegexSpecimenPartName()];
    }

}
