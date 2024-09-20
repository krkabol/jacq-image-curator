<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Herbaria;
use App\Model\Database\Entity\Photos;
use App\Model\Database\Entity\PhotosStatus;

/**
 * @method Photos|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Photos|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Photos[] findAll()
 * @method Photos[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Photos>
 */
class PhotosRepository extends AbstractRepository
{

    public function findOneByArchiveFilename(string $archiveFilename): ?Photos
    {
        return $this->findOneBy(['archiveFilename' => $archiveFilename]);
    }

    /**
     * if curator deletes a file in his bucket and the image i) is processed or ii) has Import error, then we have an "orphaned" row.
     *
     * @return Photos[]
     */
    public function getOrphananble(Herbaria $herbarium): array
    {
        return $this->findBy(['status' => [PhotosStatus::WAITING, PhotosStatus::CONTROL_ERROR], 'herbarium' => $herbarium]);
    }

}
