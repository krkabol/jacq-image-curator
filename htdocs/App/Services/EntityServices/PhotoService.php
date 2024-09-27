<?php declare(strict_types=1);

namespace App\Services\EntityServices;

use App\Model\Database\Entity\Photos;
use App\Model\Database\Entity\PhotosStatus;
use App\Model\Specimen;

class PhotoService extends BaseEntityService
{
    protected string $entityName = Photos::class;

    public function specimenHasPublicPhotos(Specimen $specimen): bool
    {
        if (count($this->getPublicPhotosOfSpecimen($specimen)) > 0) {
            return true;
        }
        return false;

    }

    public function getPublicPhotosOfSpecimen(Specimen $specimen): array
    {
        return $this->repository->findBy(['specimenId' => $specimen->specimenId, 'herbarium' => $specimen->herbarium, 'status' => $this->getPublicStatus()]);
    }

    public function getPublicPhoto(int $id): Photos
    {
        return $this->repository->findOneBy(['id' => $id, 'status' => $this->getPublicStatus()]);
    }

    protected function getPublicStatus()
    {
        return $this->entityManager->getReference(PhotosStatus::class, PhotosStatus::PUBLIC);
    }
}
