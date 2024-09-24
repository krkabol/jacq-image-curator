<?php declare(strict_types = 1);

namespace App\Model\Database;

use App\Model\Database\Repository\AbstractRepository;
use Doctrine\Persistence\ObjectRepository;
use Nettrine\ORM\EntityManagerDecorator;

class EntityManager extends EntityManagerDecorator
{

    use TRepositories;

    /**
     * @return AbstractRepository<T>|ObjectRepository<T>
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingAnyTypeHint
     * @internal
     */
    public function getRepository($className): ObjectRepository
    {
        return parent::getRepository($className);
    }

}
