<?php declare(strict_types = 1);

namespace App\Model;

use App\Model\Database\Entity\Herbaria;

class Specimen
{

    protected Herbaria $herbarium;

    protected int $specimenId;

    public function __construct(protected readonly string $specimenFullId)
    {
    }

    public function getHerbarium(): Herbaria
    {
        return $this->herbarium;
    }

    public function setHerbarium(Herbaria $herbarium): Specimen
    {
        $this->herbarium = $herbarium;

        return $this;
    }

    public function setSpecimenId(int $specimenId): Specimen
    {
        $this->specimenId = $specimenId;

        return $this;
    }

    public function getSpecimenId(): int
    {
        return $this->specimenId;
    }

}
