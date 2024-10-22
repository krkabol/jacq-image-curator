<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Entity()]
#[Table(name: 'contact', schema: 'front', options: ['comment' => 'People from herbaria, not necessary connected to repository users'])]
class Contact
{

    use TId;

    #[Column(nullable: false)]
    protected string $name;

    #[Column(nullable: false)]
    protected string $surname;

    #[Column()]
    protected string $description;

    #[Column()]
    protected string $email;

    #[ManyToOne(targetEntity: Herbaria::class, inversedBy: 'contacts')]
    #[JoinColumn(name: 'herbarium_id', referencedColumnName: 'id', nullable: false)]
    protected Herbaria $herbarium;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Contact
    {
        $this->name = $name;
        return $this;
    }

    public function getSurname(): string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): Contact
    {
        $this->surname = $surname;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): Contact
    {
        $this->description = $description;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): Contact
    {
        $this->email = $email;
        return $this;
    }

    public function getHerbarium(): Herbaria
    {
        return $this->herbarium;
    }

    public function setHerbarium(Herbaria $herbarium): Contact
    {
        $this->herbarium = $herbarium;
        return $this;
    }

    public function getFullname(): string
    {
        return $this->name . ' ' . $this->surname;
    }

}
