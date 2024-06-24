<?php declare(strict_types=1);

namespace app\Model\Database\Repository;

use app\Model\Database\Entity\Herbaria;

/**
 * @method Herbaria|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Herbaria|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Herbaria[] findAll()
 * @method Herbaria[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Herbaria>
 */
class HerbariaRepository extends AbstractRepository
{

    public function findOneByAcronym(string $acronym): ?Herbaria
    {
        return $this->getEntityManager()->createQueryBuilder('a')
            ->where('upper(a.acronym) = upper(:acronym)')
            ->setParameter('acronym', $acronym)
            ->setMaxResults(1)
            ->getQuery()
            ->execute();
    }

}
