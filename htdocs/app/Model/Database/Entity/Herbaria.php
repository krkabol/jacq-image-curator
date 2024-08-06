<?php

namespace app\Model\Database\Entity;

use app\Model\Database\Entity\Attributes\TId;
use app\Model\Database\Repository\HerbariaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HerbariaRepository::class)]
#[ORM\Table(name: 'herbaria', options: ["comment" => "List of involved herbaria"])]
class Herbaria
{
    use TId;
    #[ORM\Column(unique: true, nullable: false, options: ["comment" => "Acronym of herbarium according to Index Herbariorum"])]
    protected string $acronym;

    #[ORM\OneToMany(targetEntity: "Photos", mappedBy: "herbarium")]
    protected $photos;

    public function getAcronym(): string
    {
        return $this->acronym;
    }

    public function setAcronym(string $acronym): Herbaria
    {
        $this->acronym = $acronym;
        return $this;
    }


}
