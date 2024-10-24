<?php declare(strict_types = 1);

namespace App\Services\EntityServices;

use App\Model\Database\Entity\Photos;
use App\Model\Database\Entity\PhotosStatus;
use App\Model\Specimen;
use Doctrine\Common\Collections\Criteria;

class PhotoService extends BaseEntityService
{

    protected string $entityName = Photos::class;

    public function specimenHasPublicPhotos(Specimen $specimen): bool
    {
        return count($this->getPublicPhotosOfSpecimen($specimen)) > 0;
    }

    /**
     * @return Photos[]
     */
    public function getPublicPhotosOfSpecimen(Specimen $specimen): array
    {
        return $this->repository->findBy(['specimenId' => $specimen->getSpecimenId(), 'herbarium' => $specimen->getHerbarium(), 'status' => PhotosStatus::PASSED_PUBLIC]);
    }

    public function getPhotoReference(int $id): Photos
    {
        return $this->entityManager->getReference($this->entityName, $id);
    }

    public function getPublicPhoto(int $id): ?Photos
    {
        return $this->repository->findOneBy(['id' => $id, 'status' => PhotosStatus::PASSED_PUBLIC]);
    }

    public function getPublicStatus(): PhotosStatus
    {
        return $this->entityManager->getReference(PhotosStatus::class, PhotosStatus::PUBLIC);
    }

    public function getControlErrorStatus(): PhotosStatus
    {
        return $this->entityManager->getReference(PhotosStatus::class, PhotosStatus::CONTROL_ERROR);
    }

    public function getWaitingStatus(): PhotosStatus
    {
        return $this->entityManager->getReference(PhotosStatus::class, PhotosStatus::WAITING);
    }

    public function getPhotoWithError(int $id): ?Photos
    {
        return $this->repository->findOneBy(['id' => $id, 'herbarium' => $this->user->getIdentity()->herbarium, 'status' => $this->getControlErrorStatus()]);
    }

    public function findUnprocessedPhotoByOriginalFilename(string $filename): ?Photos
    {
        return $this->repository->findOneBy(['status' => [PhotosStatus::WAITING, PhotosStatus::CONTROL_ERROR], 'herbarium' => $this->user->getIdentity()->herbarium, 'originalFilename' => $filename]);
    }

    /**
     * @return Photos[]
     */
    public function findLastImported(): array
    {
        return $this->repository->findBy(['herbarium' => $this->user->getIdentity()->herbarium, 'status' => [PhotosStatus::CONTROL_OK, PhotosStatus::PUBLIC, PhotosStatus::HIDDEN]], ['lastEdit' => Criteria::DESC], 30);
    }

    /**
     * @return Photos[]
     */
    public function findPotentialDuplicates(Photos $photo): array
    {
        return $this->repository->findBy(['herbarium' => $photo->getHerbarium(), 'specimenId' => $photo->getSpecimenId(), 'archiveFileSize' => $photo->getArchiveFileSize(), 'status' => [PhotosStatus::CONTROL_OK, PhotosStatus::PUBLIC, PhotosStatus::HIDDEN]]);
    }

}
