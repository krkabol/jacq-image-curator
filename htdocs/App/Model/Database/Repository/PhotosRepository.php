<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Photos;

/**
 * @method Photos|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Photos|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Photos[] findAll()
 * @method Photos[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<\App\Model\Database\Entity\Photos>
 */
class PhotosRepository extends AbstractRepository
{

	public function findOneByArchiveFilename(string $archiveFilename): ?Photos
	{
		return $this->findOneBy(['archiveFilename' => $archiveFilename]);
	}

}
