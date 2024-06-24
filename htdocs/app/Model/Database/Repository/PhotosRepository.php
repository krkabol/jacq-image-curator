<?php declare(strict_types = 1);

namespace app\Model\Database\Repository;

use app\Model\Database\Entity\Photos;

/**
 * @method Photos|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Photos|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Photos[] findAll()
 * @method Photos[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<\app\Model\Database\Entity\Photos>
 */
class PhotosRepository extends AbstractRepository
{

	public function findOneByArchiveFilename(string $archiveFilename): ?Photos
	{
		return $this->findOneBy(['archiveFilename' => $archiveFilename]);
	}

}
