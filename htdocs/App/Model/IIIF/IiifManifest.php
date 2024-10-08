<?php declare(strict_types = 1);

namespace App\Model\IIIF;

use App\Model\Database\Entity\Herbaria;
use App\Model\Database\Entity\Photos;
use App\Model\Database\Repository\PhotosRepository;
use App\Model\Specimen;
use App\Model\SpecimenFactory;
use App\Services\EntityServices\PhotoService;
use App\Services\RepositoryConfiguration;
use Nette\Application\LinkGenerator;

class IiifManifest
{

    protected mixed $default;

    protected mixed $completed;

    protected int $specimenId;
    protected Specimen $specimen;

    protected Herbaria $herbarium;

    protected string $selfReferencingURL;

    /**
     * https://services.jacq.org/jacq-services/rest/iiif/manifest/1205047
     * https://iiif.jacq.org/b/?manifest=https://services.jacq.org/jacq-services/rest/iiif/manifest/1205047
     * TODO rewrite and use https://github.com/yale-web-technologies/IIIF-Manifest-Generator
     */
    public function __construct(protected readonly PhotosRepository $photosRepository, protected readonly RepositoryConfiguration $repositoryConfiguration, protected readonly LinkGenerator $linkGenerator, protected readonly PhotoService $photoService)
    {
        $filePath = '../App/Model/IIIF/v2.json';
        $this->default = json_decode(file_get_contents($filePath), true);
    }

    public function setSpecimen(Specimen $specimen): IiifManifest
    {
        $this->specimen = $specimen;
        return $this;
    }

    public function setSpecimenId(int $specimenId): IiifManifest
    {
        $this->specimenId = $specimenId;
        return $this;
    }

    public function setHerbarium(Herbaria $herbarium): IiifManifest
    {
        $this->herbarium = $herbarium;

        return $this;
    }

    public function setSelfReferencingUrl(string $selfReferencingURL): IiifManifest
    {
        $this->selfReferencingURL = $selfReferencingURL;

        return $this;
    }

    public function getCompleted(): mixed
    {
        $this->completed = $this->getDefault();
        $this->addThumbnail();
        $this->completed['sequences'][0]['canvases'] = $this->prepareCanvases();
        $this->updateSelfReferencingUrl();

        return $this->completed;
    }

    public function getDefault(): mixed
    {
        return $this->default;
    }

    public function updateSelfReferencingUrl(): IiifManifest
    {
        $this->completed['sequences'][0]['@id'] = $this->selfReferencingURL . '#sequence-1';
        $this->completed['@id'] = $this->selfReferencingURL;

        return $this;
    }

    protected function addThumbnail(): IiifManifest
    {
        $image = $this->getFirstImage();
        $this->completed['thumbnail']['@id'] = $this->repositoryConfiguration->getImageServerUrlThumbnail($image->getJp2Filename());
        $this->completed['thumbnail']['service']['@id'] = $this->repositoryConfiguration->getImageServerInfoURL($image->getJp2Filename());

        return $this;
    }

    protected function getFirstImage(): ?Photos
    {
        $photos = $this->photoService->getPublicPhotosOfSpecimen($this->specimen);
        if (count($photos) !== 0) {
            return $photos[0];
        }
        return null;

    }

    /**
     * @return mixed[]
     */
    protected function prepareCanvases(): array
    {
        $canvases = [];
        $images = $this->getImages();
        foreach ($images as $image) {
            $canvases[] = $this->mapCanvasObject($image);
        }

        return $canvases;
    }

    /**
     * @return Photos[]
     */
    protected function getImages(): array
    {
        return $this->photoService->getPublicPhotosOfSpecimen($this->specimen);
    }

    protected function mapCanvasObject(Photos $photo): mixed
    {
        $canvasObject = $this->getJsonCanvasPrototype();
        $canvasObject['@id'] = $this->repositoryConfiguration->getImageServerInfoURL($photo->getJp2Filename()) . '#canvas';
        $canvasObject['label'] = $photo->getJp2Filename();
        $canvasObject['height'] = $photo->getHeight();
        $canvasObject['width'] = $photo->getWidth();
        $canvasObject['images'][] = $this->mapImageObject($photo);

        return $canvasObject;
    }

    protected function getJsonCanvasPrototype(): mixed
    {
        return json_decode('{"@id": "https://services.jacq.org/jacq-services/rest/iiif/manifest/1205047/c/dr_047922_0",
          "@type": "sc:Canvas",
          "label": "dr_047922",
          "height": 5391,
          "width": 4146,
          "images": []
          }', true);
    }

    protected function mapImageObject(Photos $photo): mixed
    {
        $imageObject = $this->getJsonImagePrototype();
        $imageObject['@id'] = $this->repositoryConfiguration->getImageServerInfoURL($photo->getJp2Filename()) . '#image';
        $imageObject['on'] = $this->repositoryConfiguration->getImageServerInfoURL($photo->getJp2Filename()) . '#canvas';
        $imageObject['resource']['@id'] = $this->repositoryConfiguration->getImageServerInfoURL($photo->getJp2Filename());
        $imageObject['resource']['service']['@id'] = $this->repositoryConfiguration->getImageServerInfoURL($photo->getJp2Filename());
        $imageObject['resource']['height'] = $photo->getHeight();
        $imageObject['resource']['width'] = $photo->getWidth();
        $imageObject['metadata'][] = ['label' => 'Archive Master file (TIFF)', 'value' => "<a href='" . $this->linkGenerator->link('Front:Repository:archiveImage', [$photo->getId()]) . "'>download original</a>"];

        return $imageObject;
    }

    protected function getJsonImagePrototype(): mixed
    {
        return json_decode('{
              "@id": "https://services.jacq.org/jacq-services/rest/iiif/manifest/1205047/i/dr_047922_0",
              "@type": "oa:Annotation",
              "motivation": "sc:painting",
              "on": "https://services.jacq.org/jacq-services/rest/iiif/manifest/1205047/c/dr_047922_0",
              "resource": {
                "@id": "XXlink2info.jsonXXX",
                "@type": "dctypes:Image",
                "format": "image/jp2",
                "height": 5391,
                "width": 4146,
                "service": {
                  "@context": "http://iiif.io/api/image/2/context.json",
                  "@id": "XXlink2info.jsonXXX",
                  "profile": "http://iiif.io/api/image/2/level2.json",
                  "protocol": "http://iiif.io/api/image"
                }
              }
            }', true);
    }

    protected function addTiffLink(): IiifManifest
    {
        $this->completed['rendering'] = ['@id' => $this->repositoryConfiguration, 'label' => 'download full TIFF scan', 'format' => 'image/tiff'];

        return $this;
    }

}
