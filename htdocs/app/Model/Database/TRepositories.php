<?php declare(strict_types=1);

namespace App\Model\Database;
use App\Model\Database\Entity\Herbaria;
use App\Model\Database\Entity\Photos;
use App\Model\Database\Entity\PhotosStatus;
use App\Model\Database\Entity\User;

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
