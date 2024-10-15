<?php declare(strict_types = 1);

namespace App\Model\IIIF;

use App\Model\Database\EntityManager;
use App\Model\Database\Repository\PhotosRepository;
use App\Services\EntityServices\PhotoService;
use App\Services\RepositoryConfiguration;
use IIIF\PresentationAPI\Resources\Manifest;
use Nette\Application\LinkGenerator;

class ManifestFactory
{

    protected PhotosRepository $photosRepository;

    public function __construct(protected readonly EntityManager $entityManager, protected readonly RepositoryConfiguration $configuration, protected readonly LinkGenerator $linkGenerator, protected readonly PhotoService $photoService)
    {
        $this->photosRepository = $this->entityManager->getPhotosRepository();
    }

    public function prototypeV2(int $specimenId, string $herbariumAcronym, string $selfReferencingURL): IiifManifest
    {
        $herbarium = $this->entityManager->getHerbariaRepository()->findOneBy(['acronym' => $herbariumAcronym]);

        return (new IiifManifest($this->photosRepository, $this->configuration, $this->linkGenerator, $this->photoService))
            ->setSpecimenId($specimenId)
            ->setHerbarium($herbarium)
            ->setSelfReferencingUrl($selfReferencingURL);
    }

    public function createManifest(): Manifest
    {
        return new Manifest(true);
    }

}
