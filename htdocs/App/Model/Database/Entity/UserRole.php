<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;

#[Entity()]
#[Table(name: 'usersrole', options: ['comment' => 'List of available roles for users'])]
class UserRole
{

    use TId;

    public const int SUPER_ADMIN = 1;
    public const int ADMIN = 2;
    public const int USER = 3;

    #[Column(unique: true, nullable: false, options: ['comment' => 'name of the role'])]
    protected string $name;

    #[Column(unique: true, nullable: false, options: ['comment' => 'short description'])]
    protected string $description;

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

}
