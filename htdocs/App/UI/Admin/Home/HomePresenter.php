<?php declare(strict_types = 1);

namespace App\UI\Admin\Home;

use App\Model\Database\Entity\Photos;
use App\Services\CuratorService;
use App\UI\Base\SecuredPresenter;
use Nette\Application\Responses\CallbackResponse;
use Nette\Application\UI\Form;

final class HomePresenter extends SecuredPresenter
{

    /** @inject */
    public CuratorService $curatorService;

    public ?Photos $photo;

    public function renderDefault(): void
    {
        $this->template->title = 'Admin';
        $this->template->statuses = $this->curatorService->getAllStatuses();
    }

    public function renderUpload(): void
    {
        $this->template->title = 'New Files';
        $files = $this->curatorService->getAllCuratorBucketFiles($this->herbarium);
        $this->template->files = $files;
        $this->template->orphanedItems = $this->curatorService->getOrphanedItems($this->herbarium);
        $this->template->eligible = count(array_filter($files, fn ($item) => $item->isEligibleToBeImported() === true));
        $this->template->erroneous = count(array_filter($files, fn ($item) => $item->hasControlError() === true));
        $this->template->waiting = count(array_filter($files, fn ($item) => $item->isAlreadyWaiting() === true));
        $this->template->preliminaryError = count(array_filter($files, fn ($item) => $item->isSizeOK() === false || $item->isTypeOK() === false));
    }

    public function actionThumbnail(int $id): void
    {
        $thumb = $this->entityManager->getPhotosRepository()->find($id)->getThumbnail();
        if ($thumb !== null) {
            $this->sendResponse(new CallbackResponse(function ($request, $response) use ($thumb): void {
                $response->setContentType('image');
                $response->setExpiration('1 hour');
                echo stream_get_contents($thumb);
            }));
        } else {
            $this->error('Thumbnail not found');
        }
    }

    public function actionRevise(int $id): void
    {
        //tODO - find etc has to be moved into service to facilitate "my herbarium" restrictions!
        $photo = $this->curatorService->getPhotoWithError($this->herbarium, $id);
        if ($photo === null) {
            $this->error('Photo not found');
        }

        $this->template->photo = $photo;
        $this->photo = $photo;
    }

    public function renderOverview(): void
    {
        $files = $this->curatorService->getLatestImports($this->herbarium);
        $this->template->files = $files;
    }

    public function actionPrimaryImport(): void
    {
        try {
            $this->curatorService->registerNewFiles($this->herbarium);
            $this->flashMessage('Files successfully marked to be processed', 'success');
        } catch (\Throwable $exception) {
            $this->flashMessage('An error occurred: ' . $exception->getMessage(), 'danger');
        }

        $this->redirect('upload');
    }

    public function actionReimport(int $id): void
    {
        try {
            $photo = $this->curatorService->getPhotoWithError($this->herbarium, $id);
            if ($photo === null) {
                $this->error('Photo not found');
            }

            $this->curatorService->reimportPhoto($this->herbarium, $photo);
            $this->flashMessage('File successfully marked to be re-processed', 'success');
        } catch (\Throwable $exception) {
            $this->flashMessage('An error occurred: ' . $exception->getMessage(), 'danger');
        }

        $this->redirect('upload');
    }

    public function actionDelete(int $id): void
    {
        try {
            $photo = $this->curatorService->getPhotoWithError($this->herbarium, $id);
            if ($photo === null) {
                $this->error('Photo not found');
            }

            $name = $photo->getOriginalFilename();
            $this->curatorService->deleteNotImportedPhoto($this->herbarium, $photo);
            $this->flashMessage('Photo ' . $name . ' deleted.', 'success');
        } catch (\Throwable $exception) {
            $this->flashMessage('An error occurred: ' . $exception->getMessage(), 'danger');
        }

        $this->redirect(':upload');
    }

    public function specimenIdFormSucceeded(Form $form, \stdClass $values): void
    {
        try {
            $photo = $this->curatorService->getPhotoWithError($this->herbarium, (int) $values->photoId);
            if ($photo === null) {
                $this->error('Photo not found');
            }

            $this->curatorService->reimportPhoto($this->herbarium, $this->entityManager->getReference(Photos::class, $values->photoId), (string) $values->specimen);

            $fullID = $this->herbarium->getAcronym() . '-' . $values->specimen;
            $this->flashMessage('File successfully marked to be re-processed with ID ' . $fullID, 'success');
        } catch (\Throwable $exception) {
            $this->flashMessage('An error occurred: ' . $exception->getMessage(), 'danger');
        }

        $this->redirect(':upload');
    }

    protected function createComponentSpecimenIdForm(): Form
    {
        $form = new Form();
        $form->addInteger('specimen', 'ID:')
            ->setRequired('Please insert only number.')
            ->addRule($form::Integer, 'It must be integer');
        $form->addHidden('photoId', $this->photo->getId());
        $form->addSubmit('submit', 'Import with this ID');
        $form->onSuccess[] = [$this, 'specimenIdFormSucceeded'];

        return $form;
    }

}
