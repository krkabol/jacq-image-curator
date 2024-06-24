<?php

declare(strict_types=1);

namespace app\UI\Iiif;

use App\Model\Database\EntityManager;
use app\Services\S3Service;
use app\Services\StorageConfiguration;
use app\UI\Base\BasePresenter;
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

    public function beforeRender()
    {
        $this->photosRepository = $this->entityManager->getPhotosRepository();
        parent::beforeRender();
    }

    public function actionArchiveImage($id)
    {
        $bucket = $this->configuration->getArchiveBucket();
        if ($this->s3Service->objectExists($bucket, $id)) {
            $head = $this->s3Service->headObject($bucket, $id);
            $stream = $this->s3Service->getStreamOfObject($bucket, $id);

            $callback = function (Request $httpRequest, Response $httpResponse) use ($id, $head, $stream) {
                $httpResponse->addHeader("Content-Type", $head['ContentType']);
                $httpResponse->addHeader('Content-Disposition', "inline; filename" . $id);
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
        $images = $this->photosRepository->findBy(["herbarium"=>$herbarium,"specimenId"=>$specimenId]);
        if(count($images)===0){
            $this->error("Specimen ". $id ."not in evidence.");
        }
        $this->template->images = $images;
        $this->template->id = $id;
    }

}
