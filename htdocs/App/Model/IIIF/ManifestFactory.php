<?php

declare(strict_types=1);

namespace App\Model\IIIF;

use App\Model\Database\EntityManager;
use App\Services\StorageConfiguration;
use Nette\Application\LinkGenerator;

class ManifestFactory
{

    protected $photosRepository;
    protected EntityManager $entityManager;
    protected StorageConfiguration $configuration;
    protected LinkGenerator $linkGenerator;

    public function __construct(EntityManager $entityManager, StorageConfiguration $configuration, LinkGenerator $linkGenerator)
    {
        $this->entityManager = $entityManager;
        $this->photosRepository = $this->entityManager->getPhotosRepository();
        $this->configuration = $configuration;
        $this->linkGenerator = $linkGenerator;

    }

    public function prototype_v2($specimenId, $herbariumAcronym, $selfReferencingURL): IiifManifest_v2
    {
        $herbarium = $this->entityManager->getHerbariaRepository()->findOneBy(['acronym' => $herbariumAcronym]);
        return (new IiifManifest_v2($this->photosRepository, $this->configuration, $this->linkGenerator))
            ->setSpecimenId($specimenId)
            ->setHerbarium($herbarium)
            ->setSelfReferencingURL($selfReferencingURL);
    }

    public function prototype_v3(): IiifManifest_v3
    {
        return new IiifManifest_v3();
    }

}
