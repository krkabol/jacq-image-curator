<?php declare(strict_types=1);

namespace App\UI\Front\Iiif;

use App\Exceptions\SpecimenIdException;
use App\Model\IIIF\ManifestFactory;
use App\Model\Specimen;
use App\Model\SpecimenFactory;
use App\Services\EntityServices\PhotoService;
use App\UI\Base\UnsecuredPresenter;
use IIIF\PresentationAPI\Parameters\ViewingDirection;
use IIIF\PresentationAPI\Resources\Canvas;
use IIIF\PresentationAPI\Resources\Sequence;

final class IiifPresenter extends UnsecuredPresenter
{

    /** @inject */
    public SpecimenFactory $specimenFactory;
    /** @inject */
    public ManifestFactory $manifestFactory;
    /** @inject */
    public PhotoService $photoService;

    public function actionManifest(string $id): void
    {
        $specimen = $this->getSpecimen($id);

        $model = $this->manifestFactory->prototypeV2($specimen->getSpecimenId(), $specimen->getHerbarium()->getAcronym(), $this->link('//this'));
        $model->setSpecimen($specimen);
        $manifest = $model->getCompleted();
        $this->sendJson($manifest);

    }

    protected function getSpecimen(string $specimenFullId): Specimen
    {
        try {
            $specimen = $this->specimenFactory->create($specimenFullId);
        } catch (SpecimenIdException $exception) {
            $this->flashMessage($exception->getMessage(), 'error');
            $this->redirect('Home:');
        }
        if (!$this->photoService->specimenHasPublicPhotos($specimen)) {
            $this->error('Specimen has no public images', 404);
        }
        return $specimen;
    }

    public function actionManifestNew(string $id): void
    {
        $id=$this->link("//this");
//        $specimen = $this->getSpecimen($id);
        $sequence =  new Sequence();
        $canvas = new Canvas();
        $sequence->addCanvas($canvas);
        $canvas->setID("http://example.org/iiif/book1/canvas/p1");
        $canvas->addLabel("p. 1");
        $canvas->setWidth(500);
        $canvas->setHeight(500);

        $sequence->setID($id."#sequence-1")->setViewingDirection(ViewingDirection::LEFT_TO_RIGHT);
        $manifest = $this->manifestFactory->createManifest();
            $manifest->addContext("http://iiif.io/api/presentation/2/context.json")
            ->addContext("http://www.w3.org/ns/anno.jsonld")
            ->setID($this->link("//this"))
            ->addLabel("dd")
            ->setViewingDirection(ViewingDirection::LEFT_TO_RIGHT)
            ->addSequence($sequence);
        $this->sendJson($manifest->toArray());
    }

}
