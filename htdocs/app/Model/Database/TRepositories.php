<?php declare(strict_types=1);

namespace app\Model\Database;
use app\Model\Database\Entity\Herbaria;
use app\Model\Database\Entity\Photos;

/**
 * @mixin EntityManager
 */
trait TRepositories
{

    public function getPhotosRepository()
    {
        return $this->getRepository(Photos::class);
    }

    public function getHerbariaRepository()
    {
        return $this->getRepository(Herbaria::class);
    }

}
