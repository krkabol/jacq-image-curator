<?php

declare(strict_types=1);

namespace App\Model\IIIF;

use App\Model\Database\EntityManager;
use App\Model\Database\Repository\PhotosRepository;
use App\Services\StorageConfiguration;
use Nette\Application\LinkGenerator;

class ManifestFactory
{

    protected PhotosRepository $photosRepository;

    public function __construct(protected readonly EntityManager $entityManager, protected readonly StorageConfiguration $configuration, protected readonly LinkGenerator $linkGenerator)
    {
        $this->photosRepository = $this->entityManager->getPhotosRepository();
    }

    public function prototype_v2($specimenId, $herbariumAcronym, $selfReferencingURL): IiifManifest_v2
    {
        $herbarium = $this->entityManager->getHerbariaRepository()->findOneBy(['acronym' => $herbariumAcronym]);
        return (new IiifManifest_v2($this->photosRepository, $this->configuration, $this->linkGenerator))
            ->setSpecimenId($specimenId)
            ->setHerbarium($herbarium)
            ->setSelfReferencingURL($selfReferencingURL);
    }

}
