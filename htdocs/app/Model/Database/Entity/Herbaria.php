<?php

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Repository\HerbariaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HerbariaRepository::class)]
#[ORM\Table(name: 'herbaria', options: ["comment" => "List of involved herbaria"])]
class Herbaria
{
    use TId;
    #[ORM\Column(unique: true, nullable: false, options: ["comment" => "Acronym of herbarium according to Index Herbariorum"])]
    protected string $acronym;

    #[ORM\Column(unique: true, nullable: false, options: ["comment" => "S3 bucket where are stored new images before imported to the repository"])]
    protected string $bucket;

    #[ORM\OneToMany(mappedBy: "herbarium", targetEntity: "Photos")]
    protected $photos;

    #[ORM\OneToMany(mappedBy: "herbarium", targetEntity: "User")]
    protected $users;

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
