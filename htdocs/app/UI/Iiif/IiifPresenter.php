<?php

declare(strict_types=1);

namespace app\UI\Iiif;

use App\Model\Database\EntityManager;
use App\Model\IIIF\ManifestFactory;
use app\Services\S3Service;
use app\Services\StorageConfiguration;
use app\UI\Base\BasePresenter;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\CallbackResponse;
use Nette\Http\Request;
use Nette\Http\Response;


final class IiifPresenter extends BasePresenter
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

    public function actionArchiveImage($id)
    {
        $bucket = $this->configuration->getArchiveBucket();
        if ($this->s3Service->objectExists($bucket, $id)) {
            $head = $this->s3Service->headObject($bucket, $id);
            $stream = $this->s3Service->getStreamOfObject($bucket, $id);

            $callback = function (Request $httpRequest, Response $httpResponse) use ($id, $head, $stream) {
                $httpResponse->setHeader("Content-Type", $head['ContentType']);
                $httpResponse->setHeader('Content-Disposition', "inline; filename" . $id);
                fpassthru($stream);
                fclose($stream);
            };

            $response = new CallbackResponse($callback);
            $this->sendResponse($response);
        }
        $this->error("The requested image does not exists.");
    }

    public function renderSpecimen($id)
    {
        $acronym = $this->configuration->getHerbariumAcronymFromId($id);
        $specimenId = $this->configuration->getSpecimenIdFromId($id);
        $herbarium = $this->entityManager->getHerbariaRepository()->findOneByAcronym($acronym);
        $images = $this->photosRepository->findBy(["herbarium" => $herbarium, "specimenId" => $specimenId]);
        if (count($images) === 0) {
            $this->error("Specimen " . $id . "not in evidence.");
        }
        $this->template->images = $images;
        $this->template->id = $id;
    }

    /**
     * we are running http behind the proxy
     */
    protected function getAbsoluteHttpsBasePath()
    {
        $baseUrl = $this->getHttpRequest()->getUrl()->getBaseUrl();
        return preg_replace('/^http:/', 'https:', $baseUrl);
    }

    public function actionManifest($id)
    {

        $herbariumAcronym = $this->configuration->getHerbariumAcronymFromId($id);
        $specimenId = $this->configuration->getSpecimenIdFromId($id);
        $herbarium = $this->entityManager->getHerbariaRepository()->findOneByAcronym($herbariumAcronym);
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
