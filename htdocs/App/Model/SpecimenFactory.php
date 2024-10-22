<?php declare(strict_types = 1);

namespace App\Model;

use App\Exceptions\SpecimenIdException;
use App\Facades\CuratorFacade;
use App\Services\EntityServices\HerbariumService;

class SpecimenFactory
{

    public function __construct(protected readonly HerbariumService $herbariumService, protected readonly CuratorFacade $curatorFacade)
    {
    }

    public function create(string $fullSpecimenId): Specimen
    {
        if ($fullSpecimenId === '') {
            throw new SpecimenIdException('Specimen id cannot be empty');
        }

        $specimen = new Specimen($fullSpecimenId);
        $specimen->setHerbarium($this->curatorFacade->getHerbariumFromId($fullSpecimenId));

        $specimenId = $this->curatorFacade->getSpecimenIdFromId($fullSpecimenId);
        $specimen->setSpecimenId($specimenId);

        return $specimen;
    }

}
