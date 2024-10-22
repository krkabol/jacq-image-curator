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

}
