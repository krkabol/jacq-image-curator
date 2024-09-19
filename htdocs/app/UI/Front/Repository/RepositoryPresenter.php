<?php

declare(strict_types=1);

namespace App\UI\Front\Repository;

use app\Model\Database\Entity\Photos;
use App\Model\Database\EntityManager;
use app\Services\S3Service;
use app\Services\StorageConfiguration;
use App\UI\Base\UnsecuredPresenter;
use Nette\Application\Responses\CallbackResponse;
use Nette\Http\Request;
use Nette\Http\Response;


final class RepositoryPresenter extends UnsecuredPresenter
{
    /** @inject */
    public S3Service $s3Service;
    /** @inject */
    public StorageConfiguration $configuration;

    /** @inject */
    public EntityManager $entityManager;
    protected $photosRepository;


    public function startup()
    {
        $this->photosRepository = $this->entityManager->getPhotosRepository();
        parent::startup();
    }

    public function actionArchiveImage(int $id)
    {
        //TODO - preselect photos according to status -
        $photo = $this->photosRepository->getById($id);
        /** @var Photos $photo */
        $filename = $photo->getArchiveFilename();
        $bucket = $this->configuration->getArchiveBucket();
        if ($this->s3Service->objectExists($bucket, $filename)) {
            $head = $this->s3Service->headObject($bucket, $filename);
            $stream = $this->s3Service->getStreamOfObject($bucket, $filename);

            $callback = function (Request $httpRequest, Response $httpResponse) use ($filename, $head, $stream) {
                $httpResponse->setHeader("Content-Type", $head['ContentType']);
                $httpResponse->setHeader('Content-Disposition', "inline; filename" . $filename);
                fpassthru($stream);
                fclose($stream);
            };

            $response = new CallbackResponse($callback);
            $this->sendResponse($response);
        }
        $this->error("The requested image does not exists.");
    }

    public function actionSpecimen(?string $specimenFullId)
    {
        if($specimenFullId == ""){
            $this->redirect("Home:");
        }
        $acronym = $this->configuration->getHerbariumAcronymFromId($specimenFullId);
        $specimenId = $this->configuration->getSpecimenIdFromId($specimenFullId);
        $herbarium = $this->entityManager->getHerbariaRepository()->findOneWithAcronym($acronym);
        $images = $this->photosRepository->findBy(["herbarium" => $herbarium, "specimenId" => $specimenId]);
        if (count($images) === 0) {
            $this->error("Specimen " . $specimenFullId . "not in evidence.");
        }
        $this->template->images = $images;
        $this->template->id = $specimenFullId;

        $relativeLink = $this->link('Iiif:manifest', $specimenFullId);
        $this->template->manifestAbsoluteLink = $this->getAbsoluteHttpsBasePath() . ltrim($relativeLink, '/');
    }


}
