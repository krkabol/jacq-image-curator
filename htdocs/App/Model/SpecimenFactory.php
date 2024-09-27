<?php declare(strict_types=1);

namespace App\Model;

use App\Services\EntityServices\HerbariumService;
use App\Services\EntityServices\PhotoService;
use App\Services\StorageConfiguration;

class SpecimenFactory
{

    public function __construct(protected readonly StorageConfiguration $storageConfiguration, protected readonly HerbariumService $herbariumService, protected readonly PhotoService $photoService)
    {
    }

    public function create(string $fullSpecimenId): Specimen
    {
        return new Specimen($fullSpecimenId, $this->storageConfiguration, $this->herbariumService, $this->photoService);
    }

}
