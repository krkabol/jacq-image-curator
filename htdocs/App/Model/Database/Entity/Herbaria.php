<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Repository\HerbariaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToMany;
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
    protected string $logo;

    /** @var PersistentCollection<int, Photos> */
    #[OneToMany(mappedBy: 'herbarium', targetEntity: Photos::class)]
    protected PersistentCollection $photos;

    /** @var PersistentCollection<int, User> */
    #[OneToMany(mappedBy: 'herbarium', targetEntity: User::class)]
    protected PersistentCollection $users;

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

    public function getLogo(): string
    {
        return $this->logo;
    }

    public function setLogo(string $logo): Herbaria
    {
        $this->logo = $logo;

        return $this;
    }

}
