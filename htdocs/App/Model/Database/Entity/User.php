<?php

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TLastEditAt;
use App\Model\Database\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users', options: ["comment" => "Repository users"])]
class User
{
    use TId;
    use TCreatedAt;
    use TLastEditAt;

    #[ORM\Column(unique: true, nullable: false)]
    protected string $username;

    #[ORM\Column(nullable: false)]
    protected string $password;

    #[ORM\Column(nullable: false)]
    protected string $name;
    #[ORM\Column(nullable: false)]
    protected string $surname;
    #[ORM\Column(nullable: false, options: ["comment" => "User email address"])]
    protected string $email;

    #[ORM\ManyToOne(targetEntity: "Herbaria", inversedBy: "users")]
    #[ORM\JoinColumn(name: "herbarium_id", referencedColumnName: "id", nullable: false, options: ["comment" => "Herbarium"], )]
    protected Herbaria $herbarium;

    #[ORM\ManyToOne(targetEntity: "UserRole")]
    #[ORM\JoinColumn(name: "role_id", referencedColumnName: "id",  nullable: false, options: ["comment" => "Role for ACL"])]
    protected UserRole $role;

    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: ["comment" => "Option to disable access for a specific user"])]
    protected bool $active = true;

    #[ORM\Column(type: Types::TEXT, length: 60000,nullable: true, options: ["comment" => "additional information about user"])]
    protected ?string $comment;

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): User
    {
        $this->username = $username;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): User
    {
        $this->password = $password;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): User
    {
        $this->name = $name;
        return $this;
    }

    public function getSurname(): string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): User
    {
        $this->surname = $surname;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): User
    {
        $this->email = $email;
        return $this;
    }

    public function getHerbarium(): Herbaria
    {
        return $this->herbarium;
    }

    public function setHerbarium(Herbaria $herbarium): User
    {
        $this->herbarium = $herbarium;
        return $this;
    }

    public function getRole(): UserRole
    {
        return $this->role;
    }

    public function setRole(UserRole $role): User
    {
        $this->role = $role;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): User
    {
        $this->active = $active;
        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): User
    {
        $this->comment = $comment;
        return $this;
    }

    public function getFullname():string
    {
        return $this->getName()." ".$this->getSurname();
    }

}
