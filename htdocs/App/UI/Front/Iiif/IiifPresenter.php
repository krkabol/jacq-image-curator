<?php declare(strict_types=1);

namespace App\UI\Front\Iiif;

use App\Exceptions\SpecimenIdException;
use App\Model\IIIF\ManifestFactory;
use App\Model\SpecimenFactory;
use App\Services\EntityServices\PhotoService;
use App\UI\Base\UnsecuredPresenter;

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

        try {
            $specimen = $this->specimenFactory->create($id);
        } catch (SpecimenIdException $exception) {
            $this->flashMessage($exception->getMessage(), 'error');
            $this->redirect('Home:');
        }
        if (!$this->photoService->specimenHasPublicPhotos($specimen)) {
            $this->error('Specimen has no public images', 404);
        }
        $model = $this->manifestFactory->prototypeV2($specimen->specimenId, $specimen->herbarium->getAcronym(), $absoluteLink);
        $model->setSpecimen($specimen);
        $manifest = $model->getCompleted();
        $this->sendJson($manifest);

    }

}
