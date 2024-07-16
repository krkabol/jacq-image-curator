<?php

declare(strict_types=1);

namespace App\Model\IIIF;

use app\Model\Database\Entity\Herbaria;
use app\Model\Database\Entity\Photos;
use app\Model\Database\Repository\PhotosRepository;
use app\Services\StorageConfiguration;
use Nette\Utils\Strings;

class IiifManifest_v2
{
    protected $default;
    protected $completed;
    protected $specimenId;
    protected Herbaria $herbarium;
    protected string $selfReferencingURL;

    /** @var PhotosRepository */
    protected $photosRepository;
    protected StorageConfiguration $storageConfiguration;

    public function __construct($repository, StorageConfiguration $configuration)
    {
        $this->photosRepository = $repository;
        $this->storageConfiguration = $configuration;
        $filePath = '../app/Model/IIIF/v2.json'; //https://services.jacq.org/jacq-services/rest/iiif/manifest/1205047
//        https://iiif.jacq.org/b/?manifest=https://services.jacq.org/jacq-services/rest/iiif/manifest/1205047
        $this->default = json_decode(file_get_contents($filePath), true);
    }

    public function setSpecimenId($specimenId): IiifManifest_v2
    {
        $this->specimenId = $specimenId;
        return $this;
    }

    public function setHerbarium(Herbaria $herbarium): IiifManifest_v2
    {
        $this->herbarium = $herbarium;
        return $this;
    }

    public function setSelfReferencingURL(string $selfReferencingURL): IiifManifest_v2
    {
        $this->selfReferencingURL = $selfReferencingURL;
        return $this;
    }



    public function getCompleted()
    {
        $this->completed = $this->getDefault();
        $this->addThumbnail();
        $this->completed["sequences"][0]["canvases"] = $this->prepareCanvases();
        $this->updateSelfReferencingURL();
        return $this->completed;
    }

    public function getDefault()
    {
        return $this->default;
    }

    protected function addTiffLink():IiifManifest_v2
    {
        $this->completed["rendering"]=array("@id"=>$this->storageConfiguration, "label"=>"download full TIFF scan", "format"=>"image/tiff");
        return $this;
    }

    protected function addThumbnail(): IiifManifest_v2
    {
        $image = $this->getFirstImage();
        $this->completed["thumbnail"]["@id"] = $this->storageConfiguration->getImageIIIFURL4Thumbnail($image->getJp2Filename());
        $this->completed["thumbnail"]["service"]["@id"] = $this->storageConfiguration->getImageIIIFInfoURL($image->getJp2Filename());
        return $this;
    }

    protected function getFirstImage(): Photos
    {
        return $this->photosRepository->findOneBy(['specimenId' => $this->specimenId, 'herbarium' => $this->herbarium]);
    }

    protected function prepareCanvases(): array
    {
        $canvases=[];
        $images = $this->getImages();
        foreach ($images as $image) {
            $canvases[] = $this->mapCanvasObject($image);
        }
        return $canvases;
    }

    protected function getImages(): array
    {
        return $this->photosRepository->findBy(['specimenId' => $this->specimenId, 'herbarium' => $this->herbarium]);
    }

    protected function mapImageObject(Photos $photo)
    {
        $imageObject = $this->getJSONImagePrototype();
        $imageObject["@id"] = $this->storageConfiguration->getImageIIIFInfoURL($photo->getJp2Filename()) . "#image";
        $imageObject["on"] = $this->storageConfiguration->getImageIIIFInfoURL($photo->getJp2Filename()) . "#canvas";
        $imageObject["resource"]["@id"] = $this->storageConfiguration->getImageIIIFInfoURL($photo->getJp2Filename());
        $imageObject["resource"]["service"]["@id"] = $this->storageConfiguration->getImageIIIFInfoURL($photo->getJp2Filename());
        $imageObject["resource"]["height"] = $photo->getHeight();
        $imageObject["resource"]["width"] = $photo->getWidth();
        return $imageObject;
    }

    protected function mapCanvasObject(Photos $photo)
    {
        $canvasObject = $this->getJSONCanvasPrototype();
        $canvasObject["@id"] = $this->storageConfiguration->getImageIIIFInfoURL($photo->getJp2Filename()) . "#canvas";
        $canvasObject["label"] = $photo->getFullSpecimenId();
        $canvasObject["height"] = $photo->getHeight();
        $canvasObject["width"] = $photo->getWidth();
        $canvasObject["images"][] = $this->mapImageObject($photo);
        return $canvasObject;
    }

    protected function getJSONImagePrototype()
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

    protected function getJSONCanvasPrototype()
    {
        return json_decode('{"@id": "https://services.jacq.org/jacq-services/rest/iiif/manifest/1205047/c/dr_047922_0",
          "@type": "sc:Canvas",
          "label": "dr_047922",
          "height": 5391,
          "width": 4146,
          "images": []
          }', true);
    }

    public function updateSelfReferencingURL()
    {
        $this->completed["sequences"][0]["@id"] = $this->selfReferencingURL."#sequence-1";
        $this->completed["@id"] = $this->selfReferencingURL;
        return $this;
    }
}
