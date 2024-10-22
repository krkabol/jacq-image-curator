<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Repository\HerbariaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OrderBy;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\PersistentCollection;

#[Entity(repositoryClass: HerbariaRepository::class)]
#[Table(name: 'herbaria', options: ['comment' => 'List of involved herbaria'])]
class Herbaria
{

    use TId;

    #[Column(unique: true, nullable: false, options: ['comment' => 'Acronym of herbarium according to Index Herbariorum'])]
    protected string $acronym;

    #[Column(unique: true, nullable: false, options: ['comment' => 'S3 bucket where are stored new images before imported to the repository'])]
    protected string $bucket;

    #[Column(type: Types::TEXT, length: 5000, unique: false, nullable: true, options: ['comment' => 'logo URL'])]
    protected ?string $logo;

    #[Column(type: Types::TEXT, length: 5000, unique: false, nullable: true, options: ['comment' => 'full name of the herbarium'])]
    protected ?string $fullname;

    #[Column(type: Types::TEXT, length: 5000, unique: false, nullable: true, options: ['comment' => 'address of the institution/herbarium'])]
    protected ?string $address;

    /** @var PersistentCollection<int, Photos> */
    #[OneToMany(mappedBy: 'herbarium', targetEntity: Photos::class)]
    protected PersistentCollection $photos;

    /** @var PersistentCollection<int, User> */
    #[OneToMany(mappedBy: 'herbarium', targetEntity: User::class)]
    protected PersistentCollection $users;

    /** @var PersistentCollection<int, Contact> */
    #[OneToMany(mappedBy: 'herbarium', targetEntity: Contact::class)]
    #[OrderBy(["surname" => "ASC"])]
    protected PersistentCollection $contacts;

    public function getAcronym(): string
    {
        return $this->acronym;
    }

    public function setAcronym(string $acronym): Herbaria
    {
        $this->acronym = $acronym;

        return $this;
    }

    public function getBucket(): string
    {
        return $this->bucket;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(string $logo): Herbaria
    {
        $this->logo = $logo;

        return $this;
    }

    public function getFullname(): ?string
    {
        return $this->fullname;
    }

    public function setFullname(string $fullname): Herbaria
    {
        $this->fullname = $fullname;
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): Herbaria
    {
        $this->address = $address;
        return $this;
    }

    public function getContacts(): PersistentCollection
    {
        return $this->contacts;
    }

    public function setContacts(PersistentCollection $contacts): Herbaria
    {
        $this->contacts = $contacts;
        return $this;
    }

}
