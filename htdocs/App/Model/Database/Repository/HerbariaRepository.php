<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Herbaria;

/**
 * @method Herbaria|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Herbaria|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Herbaria[] findAll()
 * @method Herbaria[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Herbaria>
 */
final class HerbariaRepository extends AbstractRepository
{

    public function findOneWithAcronym(string $acronym): ?Herbaria
    {
        return $this->createQueryBuilder('a')
            ->where('upper(a.acronym) = upper(:acronym)')
            ->setParameter('acronym', $acronym)
            ->getQuery()
            ->getOneOrNullResult();
    }

}
