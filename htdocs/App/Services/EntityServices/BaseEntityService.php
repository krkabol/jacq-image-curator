<?php declare(strict_types = 1);

namespace App\Services\EntityServices;

use App\Model\Database\EntityManager;
use App\Model\Database\Repository\AbstractRepository;
use Doctrine\Persistence\ObjectRepository;
use Nette\Security\User;

abstract class BaseEntityService
{

    protected AbstractRepository|ObjectRepository $repository;

    protected string $entityName;

    public function __construct(protected readonly EntityManager $entityManager, protected readonly User $user)
    {
        $this->repository = $this->entityManager->getRepository($this->entityName);
    }

    /**
     * @return \App\Model\Database\T[]|array|object[]
     */
    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    /**
     * @param array $criteria
     * @param array $orderBy
     * @return object|\App\Model\Database\T|null
     */
    public function findOneBy(array $criteria, array $orderBy = []): ?object
    {
        return $this->repository->findOneBy($criteria, $orderBy);
    }

}
