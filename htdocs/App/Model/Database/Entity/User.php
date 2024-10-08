<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TLastEditAt;
use App\Model\Database\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Entity(repositoryClass: UserRepository::class)]
#[Table(name: 'users', options: ['comment' => 'Repository users'])]
class User
{

    use TId;
    use TCreatedAt;
    use TLastEditAt;

    #[Column(unique: true, nullable: false)]
    protected string $username;

    #[Column(nullable: false)]
    protected string $password;

    #[Column(nullable: false)]
    protected string $name;

    #[Column(nullable: false)]
    protected string $surname;

    #[Column(nullable: false, options: ['comment' => 'User email address'])]
    protected string $email;

    #[ManyToOne(targetEntity: Herbaria::class, inversedBy: 'users')]
    #[JoinColumn(name: 'herbarium_id', referencedColumnName: 'id', nullable: false, options: ['comment' => 'Herbarium'],)]
    protected Herbaria $herbarium;

    #[ManyToOne(targetEntity: UserRole::class)]
    #[JoinColumn(name: 'role_id', referencedColumnName: 'id', nullable: false, options: ['comment' => 'Role for ACL'])]
    protected UserRole $role;

    #[Column(type: Types::BOOLEAN, nullable: false, options: ['comment' => 'Option to disable access for a specific user'])]
    protected bool $active = true;

    #[Column(type: Types::TEXT, length: 60000, nullable: true, options: ['comment' => 'additional information about user'])]
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

    public function getFullname(): string
    {
        return $this->getName() . ' ' . $this->getSurname();
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

}
