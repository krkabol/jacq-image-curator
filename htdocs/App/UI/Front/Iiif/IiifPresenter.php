<?php

declare(strict_types=1);

namespace App\UI\Front\Iiif;

use App\Model\Database\EntityManager;
use App\Model\IIIF\ManifestFactory;
use App\Services\S3Service;
use App\Services\StorageConfiguration;
use App\UI\Base\UnsecuredPresenter;
use Nette\Application\Responses\CallbackResponse;
use Nette\Http\Request;
use Nette\Http\Response;


final class IiifPresenter extends UnsecuredPresenter
{
    /** @inject */
    public S3Service $s3Service;
    /** @inject */
    public StorageConfiguration $configuration;

    /** @inject */
    public EntityManager $entityManager;
    protected $photosRepository;

    /** @inject  */
    public ManifestFactory $manifestFactory;

    public function startup()
    {
        $this->photosRepository = $this->entityManager->getPhotosRepository();
        parent::startup();
    }


    public function actionManifest($id)
    {
        $herbariumAcronym = $this->configuration->getHerbariumAcronymFromId($id);
        $specimenId = $this->configuration->getSpecimenIdFromId($id);
        $herbarium = $this->entityManager->getHerbariaRepository()->findOneWithAcronym($herbariumAcronym);
        $specimen = $this->photosRepository->findOneBy(['specimenId' => $specimenId, 'herbarium' => $herbarium]);
        if ($specimen === null){
            $this->error('Specimen not found', 404);
        }

        $relativeLink = $this->link('this');
        $absoluteLink = $this->getAbsoluteHttpsBasePath() . ltrim($relativeLink, '/');

        $model = $this->manifestFactory->prototype_v2($specimenId,$herbariumAcronym,$absoluteLink);

        $manifest = $model->getCompleted();
        $this->sendJson($manifest);
    }

    public function actionManifestv3()
    {
        $model = $this->manifestFactory->prototype_v3();
        $relativeLink = $this->link('this');
        $baseUrl = $this->getHttpRequest()->getUrl()->getBaseUrl();
        $httpBaseUrl = preg_replace('/^http:/', 'https:', $baseUrl);
        $absoluteLink = $httpBaseUrl . ltrim($relativeLink, '/');
        $model["id"] = $absoluteLink;

        $this->sendJson($model);
    }

}
