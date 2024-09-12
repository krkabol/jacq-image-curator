<?php

declare(strict_types=1);

namespace App\UI\Admin\Home;

use App\Services\CuratorService;
use app\Services\ImageService;
use app\UI\Base\SecuredPresenter;

final class HomePresenter extends SecuredPresenter
{
    /** @inject */
    public ImageService $imageService;

    /** @inject */
    public CuratorService $curatorService;

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

    public function renderOverview()
    {
        $files = $this->curatorService->getLatestImports($this->getUser()->getIdentity()->herbarium);
        $this->template->files = $files;
    }

    public function renderDryrun()
    {
        $result = $this->imageService->proceedDryrun();
        $this->setView("proceed");
        $this->template->success = $result[0];
        $this->template->error = $result[1];
    }

    public function actionPrimaryImport()
    {
        try {
            $this->curatorService->registerNewFiles($this->getUser()->getIdentity()->herbarium);
            $this->flashMessage("Files successfully marked to be processed", "success");
        }catch (\Exception $exception){
            $this->flashMessage("An error occured: ".$exception->getMessage(), "danger");
        }
        $this->redirect("upload");
    }
}
