<?php declare(strict_types=1);

namespace app\Model\Database;
use app\Model\Database\Entity\Herbaria;
use app\Model\Database\Entity\Photos;
use app\Model\Database\Entity\PhotosStatus;
use app\Model\Database\Entity\User;

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

    public function getUserRepository()
    {
        return $this->getRepository(User::class);
    }

    public function getPhotosStatusRepository()
    {
        return $this->getRepository(PhotosStatus::class);
    }

}
