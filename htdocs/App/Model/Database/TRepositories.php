<?php declare(strict_types = 1);

namespace App\Model\Database;

use App\Model\Database\Entity\Contact;
use App\Model\Database\Entity\Herbaria;
use App\Model\Database\Entity\Photos;
use App\Model\Database\Entity\PhotosStatus;
use App\Model\Database\Entity\User;
use App\Model\Database\Repository\HerbariaRepository;
use App\Model\Database\Repository\PhotosRepository;
use App\Model\Database\Repository\UserRepository;
use Doctrine\ORM\EntityRepository;

/**
 * @mixin EntityManager
 */
trait TRepositories
{

    public function getPhotosRepository(): PhotosRepository
    {
        return $this->getRepository(Photos::class);
    }

    public function getHerbariaRepository(): HerbariaRepository
    {
        return $this->getRepository(Herbaria::class);
    }

    public function getUserRepository(): UserRepository
    {
        return $this->getRepository(User::class);
    }

    public function getPhotosStatusRepository(): EntityRepository
    {
        return $this->getRepository(PhotosStatus::class);
    }

    public function getContactRepository(): EntityRepository
    {
        return $this->getRepository(Contact::class);
    }

}
