<?php declare(strict_types = 1);

namespace App\UI\Front\Iiif;

use App\Model\Database\EntityManager;
use App\Model\Database\Repository\PhotosRepository;
use App\Model\IIIF\ManifestFactory;
use App\Services\S3Service;
use App\Services\StorageConfiguration;
use App\UI\Base\UnsecuredPresenter;

final class IiifPresenter extends UnsecuredPresenter
{

    /** @inject */
    public S3Service $s3Service;

    /** @inject */
    public StorageConfiguration $configuration;

    /** @inject */
    public EntityManager $entityManager;

    /** @inject */
    public ManifestFactory $manifestFactory;

    protected PhotosRepository $photosRepository;

    public function startup(): void
    {
        $this->photosRepository = $this->entityManager->getPhotosRepository();

        parent::startup();
    }

    public function actionManifest(string $id): void
    {
        $herbariumAcronym = $this->configuration->getHerbariumAcronymFromId($id);
        $specimenId = $this->configuration->getSpecimenIdFromId($id);
        $herbarium = $this->entityManager->getHerbariaRepository()->findOneWithAcronym($herbariumAcronym);
        $specimen = $this->photosRepository->findOneBy(['specimenId' => $specimenId, 'herbarium' => $herbarium]);
        if ($specimen === null) {
            $this->error('Specimen not found', 404);
        }

        $relativeLink = $this->link('this');
        $absoluteLink = $this->getAbsoluteHttpsBasePath() . ltrim($relativeLink, '/');

        $model = $this->manifestFactory->prototypeV2($specimenId, $herbariumAcronym, $absoluteLink);

        $manifest = $model->getCompleted();
        $this->sendJson($manifest);
    }

}
