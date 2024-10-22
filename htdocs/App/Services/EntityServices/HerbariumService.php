<?php declare(strict_types = 1);

namespace App\Services\EntityServices;

use App\Model\Database\Entity\Herbaria;

class HerbariumService extends BaseEntityService
{

    protected string $entityName = Herbaria::class;

    public function getCurrentUserHerbarium(): Herbaria
    {
        return $this->entityManager->getReference($this->entityName, $this->user->getIdentity()->herbarium);
    }

    public function findOneWithAcronym(string $acronym): ?Herbaria
    {
        return $this->repository->findOneWithAcronym($acronym);
    }

}
