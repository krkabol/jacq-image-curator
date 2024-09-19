<?php

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity()]
#[ORM\Table(name: 'usersrole', options: ["comment" => "List of available roles for users"])]
class UserRole
{
    use TId;
    #[ORM\Column(unique: true, nullable: false, options: ["comment" => "name of the role"])]
    protected string $name;
    #[ORM\Column(unique: true, nullable: false, options: ["comment" => "short description"])]
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
