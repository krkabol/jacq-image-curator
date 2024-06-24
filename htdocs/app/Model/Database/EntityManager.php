<?php declare(strict_types = 1);

namespace App\Model\Database;

use app\Model\Database\Repository\AbstractRepository;
use Doctrine\Persistence\ObjectRepository;
use Nettrine\ORM\EntityManagerDecorator;

class EntityManager extends EntityManagerDecorator
{

	use TRepositories;

	/**
	 * @param string $entityName
	 * @return AbstractRepository<T>|ObjectRepository<T>
	 * @internal
	 */
	public function getRepository($entityName): ObjectRepository
	{
		return parent::getRepository($entityName);
	}

}
