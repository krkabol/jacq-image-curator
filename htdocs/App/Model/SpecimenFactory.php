<?php declare(strict_types=1);

namespace App\Model;

use App\Facades\CuratorFacade;
use App\Services\EntityServices\HerbariumService;
use App\Services\EntityServices\PhotoService;
use App\Services\RepositoryConfiguration;

class SpecimenFactory
{

    public function __construct(protected readonly RepositoryConfiguration $storageConfiguration, protected readonly HerbariumService $herbariumService, protected readonly PhotoService $photoService, protected readonly CuratorFacade $curatorService)
    {
    }

    public function create(string $fullSpecimenId): Specimen
    {//TODO refactor
        return new Specimen($fullSpecimenId, $this->herbariumService, $this->photoService, $this->curatorService);
    }

}
