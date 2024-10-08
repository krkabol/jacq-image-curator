<?php declare(strict_types=1);

namespace App\Model;

use App\Exceptions\SpecimenIdException;
use App\Facades\CuratorFacade;
use App\Model\Database\Entity\Herbaria;
use App\Services\EntityServices\HerbariumService;
use App\Services\EntityServices\PhotoService;

class Specimen
{

    public readonly Herbaria $herbarium;
    public readonly int $specimenId;

    public function __construct(protected readonly string $specimenFullId, protected readonly HerbariumService $herbariumService, protected readonly PhotoService $photoService, protected readonly CuratorFacade $curatorService)
    { //TODO refactor
        if ($specimenFullId == '') {
            throw new SpecimenIdException('Specimen id cannot be empty');
        }
        $herbariumAcronym = $this->curatorService->getHerbariumAcronymFromId($specimenFullId);
        $this->specimenId = $this->curatorService->getSpecimenIdFromId($specimenFullId);
        $this->herbarium = $this->herbariumService->findOneWithAcronym($herbariumAcronym);
    }

}
