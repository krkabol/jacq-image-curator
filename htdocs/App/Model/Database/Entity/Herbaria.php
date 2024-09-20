<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Repository\HerbariaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;

#[Entity(repositoryClass: HerbariaRepository::class)]
#[Table(name: 'herbaria', options: ['comment' => 'List of involved herbaria'])]
class Herbaria
{

    use TId;

    #[Column(unique: true, nullable: false, options: ['comment' => 'Acronym of herbarium according to Index Herbariorum'])]
    protected string $acronym;

    #[Column(unique: true, nullable: false, options: ['comment' => 'S3 bucket where are stored new images before imported to the repository'])]
    protected string $bucket;

    #[OneToMany(mappedBy: 'herbarium', targetEntity: 'Photos')]
    protected ArrayCollection $photos;

    #[OneToMany(mappedBy: 'herbarium', targetEntity: 'User')]
    protected ArrayCollection $users;

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

}
