<?php declare(strict_types=1);

namespace App\UI\Front\Iiif;

use App\Exceptions\SpecimenIdException;
use App\Model\IIIF\ManifestFactory;
use App\Model\Specimen;
use App\Model\SpecimenFactory;
use App\Services\EntityServices\PhotoService;
use App\UI\Base\UnsecuredPresenter;
use IIIF\PresentationAPI\Parameters\ViewingDirection;

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
        $relativeLink = $this->link('this');
        $absoluteLink = $this->getAbsoluteHttpsBasePath() . ltrim($relativeLink, '/');
        $specimen = $this->getSpecimen($id);

        $model = $this->manifestFactory->prototypeV2($specimen->getSpecimenId(), $specimen->getHerbarium()->getAcronym(), $absoluteLink);
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
        $specimen = $this->getSpecimen($id);
        $manifest = $this->manifestFactory->createManifest();
            $manifest->addContext("http://iiif.io/api/presentation/2/context.json")
            ->addContext("http://www.w3.org/ns/anno.jsonld")
            ->setID("654")
            ->addLabel("dd")
            ->setViewingDirection(ViewingDirection::LEFT_TO_RIGHT);
        $this->sendJson($manifest->toArray());
    }

}
