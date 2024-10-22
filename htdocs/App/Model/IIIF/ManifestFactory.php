<?php declare(strict_types = 1);

namespace App\Model\IIIF;

use App\Model\Database\Entity\Photos;
use App\Model\Database\EntityManager;
use App\Model\Database\Repository\PhotosRepository;
use App\Model\Specimen;
use App\Services\EntityServices\PhotoService;
use App\Services\RepositoryConfiguration;
use IIIF\PresentationAPI\Links\Service;
use IIIF\PresentationAPI\Metadata\Metadata;
use IIIF\PresentationAPI\Parameters\DCType;
use IIIF\PresentationAPI\Parameters\ViewingDirection;
use IIIF\PresentationAPI\Properties\Logo;
use IIIF\PresentationAPI\Properties\Thumbnail;
use IIIF\PresentationAPI\Resources\Annotation;
use IIIF\PresentationAPI\Resources\Canvas;
use IIIF\PresentationAPI\Resources\Content;
use IIIF\PresentationAPI\Resources\Manifest;
use IIIF\PresentationAPI\Resources\Sequence;
use Nette\Application\LinkGenerator;

class ManifestFactory
{

    protected PhotosRepository $photosRepository;

    protected Specimen $specimen;

    protected string $selfReferencingLink;

    public function __construct(protected readonly EntityManager $entityManager, protected readonly RepositoryConfiguration $repositoryConfiguration, protected readonly LinkGenerator $linkGenerator, protected readonly PhotoService $photoService)
    {
        $this->photosRepository = $this->entityManager->getPhotosRepository();
    }

    public function createManifest(Specimen $specimen, string $selfReferencingLink): Manifest
    {
        $this->specimen = $specimen;
        $this->selfReferencingLink = $selfReferencingLink;
        $manifest = new Manifest(true);
        $manifest
            ->addContext('http://www.w3.org/ns/anno.jsonld')
            ->setID($selfReferencingLink)
            ->addLabel('herbarium specimen')
            ->addDescription('A preserved herbarium specimen')
            ->setViewingDirection(ViewingDirection::LEFT_TO_RIGHT)
            ->addThumbnail($this->createThumbnail())
            ->addSequence($this->createSequence());

        if ($specimen->getHerbarium()->getLogo() !== null) {
            $manifest->addLogo((new Logo())->setID($specimen->getHerbarium()->getLogo()));
        }

        return $manifest;
    }

    protected function createThumbnail(): Thumbnail
    {
        $photo = $this->getFirstImage();
        $thumbnail = new Thumbnail();
        $thumbnail
            ->setID($this->repositoryConfiguration->getImageServerUrlThumbnail($photo->getJp2Filename()))
            ->setService($this->createService($photo));

        return $thumbnail;
    }

    protected function createService(Photos $photo): Service
    {
        $service = new Service();
        $service
            ->setID($this->repositoryConfiguration->getImageServerInfoUrl($photo->getJp2Filename()))
            ->setProfile('http://iiif.io/api/image/2/level2.json');

        return $service;
    }

    protected function getFirstImage(): ?Photos
    {
        $photos = $this->photoService->getPublicPhotosOfSpecimen($this->specimen);
        if (count($photos) !== 0) {
            return $photos[0];
        }

        return null;
    }

    protected function createSequence(): Sequence
    {
        $sequence = new Sequence();

        $sequence
            ->setID($this->selfReferencingLink . '#sequence-1')
            ->setViewingDirection(ViewingDirection::LEFT_TO_RIGHT)
            ->addLabel('Current order');

        foreach ($this->getImages() as $image) {
            $sequence->addCanvas($this->createCanvas($image));
        }

        return $sequence;
    }

    /**
     * @return Photos[]
     */
    protected function getImages(): array
    {
        return $this->photoService->getPublicPhotosOfSpecimen($this->specimen);
    }

    protected function createCanvas(Photos $photo): Canvas
    {
        $canvas = new Canvas();
        $metadata = new Metadata();
        $metadata->addLabelValue('Archive Master file (TIFF)', "<a href='" . $this->linkGenerator->link('Front:Repository:archiveImage', [$photo->getId()]) . "'>download original</a>");
        $canvas
            ->setID($this->repositoryConfiguration->getImageServerInfoUrl($photo->getJp2Filename()) . '#canvas')
            ->addLabel($photo->getJp2Filename())
            ->setWidth($photo->getWidth())
            ->setHeight($photo->getHeight())
            ->setMetadata($metadata)
            ->addImage($this->createAnnotation($photo));

        return $canvas;
    }

    protected function createAnnotation(Photos $photo): Annotation
    {
        $content = new Content();
        $content
            ->setID($this->repositoryConfiguration->getImageServerInfoUrl($photo->getJp2Filename()))
            ->setType(DCType::IMAGE)
            ->setFormat('image/jp2')
            ->setWidth($photo->getWidth())
            ->setHeight($photo->getHeight())
            ->addService($this->createService($photo));

        $annotation = new Annotation();
        $annotation
            ->setID($this->repositoryConfiguration->getImageServerInfoUrl($photo->getJp2Filename()) . '#image')
            ->setOn($this->repositoryConfiguration->getImageServerInfoUrl($photo->getJp2Filename()) . '#canvas')
            ->setContent($content);

        return $annotation;
    }

}
