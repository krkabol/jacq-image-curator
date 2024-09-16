<?php

declare(strict_types=1);

namespace App\UI\Admin\Home;

use App\Model\Database\EntityManager;
use App\Services\CuratorService;
use app\UI\Base\SecuredPresenter;
use Nette\Application\Responses\CallbackResponse;
final class HomePresenter extends SecuredPresenter
{

    /** @inject */
    public CuratorService $curatorService;
    /** @inject */
    public EntityManager $entityManager;

    public function renderDefault()
    {
        $this->template->title = 'Admin';
        $this->template->statuses = $this->curatorService->getAllStatuses();
    }

    public function renderUpload()
    {
        $this->template->title = 'New Files';
        $files = $this->curatorService->getAllCuratorBucketFiles($this->getUser()->getIdentity()->herbarium);
        $this->template->files = $files;
        $this->template->orphanedItems = $this->curatorService->getOrphanedItems($this->getUser()->getIdentity()->herbarium);
        $this->template->eligible = count(array_filter($files, function ($item) {
            return $item->isEligibleToBeImported() === true;
        }));
        $this->template->erroneous = count(array_filter($files, function ($item) {
            return $item->hasControlError() === true;
        }));
        $this->template->waiting = count(array_filter($files, function ($item) {
            return $item->isAlreadyWaiting() === true;
        }));
        $this->template->preliminaryError = count(array_filter($files, function ($item) {
            return ($item->isSizeOK() === false || $item->isTypeOK() === false);
        }));

    }

    public function actionThumbnail($id)
    {
        $thumb = $this->entityManager->getPhotosRepository()->find($id)->getThumbnail();
        if ($thumb !== null) {
            $this->sendResponse(new CallbackResponse(function ($request, $response) use ($thumb) {
                $response->setContentType("image");
                $response->setExpiration('1 hour');
                echo stream_get_contents($thumb);
            }));
        } else {
            $this->error('Thumbnail not found');
        }

    }

    public function renderRevise($id)
    {
       $this->template->photo = $this->entityManager->getPhotosRepository()->find($id);

    }

    public function renderOverview()
    {
        $files = $this->curatorService->getLatestImports($this->getUser()->getIdentity()->herbarium);
        $this->template->files = $files;
    }

    public function actionPrimaryImport()
    {
        try {
            $this->curatorService->registerNewFiles($this->getUser()->getIdentity()->herbarium);
            $this->flashMessage("Files successfully marked to be processed", "success");
        } catch (\Exception $exception) {
            $this->flashMessage("An error occured: " . $exception->getMessage(), "danger");
        }
        $this->redirect("upload");
    }
}
