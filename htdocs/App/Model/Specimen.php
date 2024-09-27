<?php declare(strict_types=1);

namespace App\Model;

use App\Model\Database\Entity\Herbaria;
use App\Services\EntityServices\HerbariumService;
use App\Services\EntityServices\PhotoService;
use App\Services\StorageConfiguration;

class Specimen
{

    public readonly Herbaria $herbarium;
    public readonly int $specimenId;

    public function __construct(protected readonly string $specimenFullId, protected readonly StorageConfiguration $storageConfiguration, protected readonly HerbariumService $herbariumService, protected readonly PhotoService $photoService)
    {
        if ($specimenFullId == '') {
            throw new SpecimenIdException('Specimen id cannot be empty');
        }
        $herbariumAcronym = $this->storageConfiguration->getHerbariumAcronymFromId($specimenFullId);
        $this->specimenId = $this->storageConfiguration->getSpecimenIdFromId($specimenFullId);
        $this->herbarium = $this->herbariumService->findOneWithAcronym($herbariumAcronym);
    }

}
