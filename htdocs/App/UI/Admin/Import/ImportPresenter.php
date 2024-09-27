<?php declare(strict_types = 1);

namespace App\UI\Admin\Import;

use App\Facades\CuratorFacade;
use App\Model\Database\Entity\Photos;
use App\Services\EntityServices\PhotoService;
use App\UI\Base\SecuredPresenter;
use Nette\Application\Responses\CallbackResponse;
use Nette\Application\UI\Form;

final class ImportPresenter extends SecuredPresenter
{

    /** @inject */
    public CuratorFacade $curatorService;
    /** @inject  */
    public PhotoService $photoService;

    public ?Photos $photo;

    public function renderDefault(): void
    {
        $this->template->title = 'New Files';
        $files = $this->curatorService->getAllCuratorBucketFiles();
        $this->template->files = $files;
        $this->template->orphanedItems = $this->curatorService->getOrphanedItems($this->herbarium);
        $this->template->eligible = count(array_filter($files, fn ($item) => $item->isEligibleToBeImported() === true));
        $this->template->erroneous = count(array_filter($files, fn ($item) => $item->hasControlError() === true));
        $this->template->waiting = count(array_filter($files, fn ($item) => $item->isAlreadyWaiting() === true));
        $this->template->preliminaryError = count(array_filter($files, fn ($item) => $item->isSizeOK() === false || $item->isTypeOK() === false));
    }

    public function actionThumbnail(int $id): void
    {
        $thumb = $this->photoService->getPhotoWithError($id)?->getThumbnail();
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
        $photo = $this->photoService->getPhotoWithError($id);
        if ($photo === null) {
            $this->error('Photo not found');
        }

        $this->template->photo = $photo;
        $this->photo = $photo;
    }

      public function actionPrimaryImport(): void
    {
        try {
            $this->curatorService->registerNewFiles();
            $this->flashMessage('Files successfully marked to be processed', 'success');
        } catch (\Throwable $exception) {
            $this->flashMessage('An error occurred: ' . $exception->getMessage(), 'danger');
        }

        $this->redirect('upload');
    }

    public function actionReimport(int $id): void
    {
        try {
            $photo = $this->photoService->getPhotoWithError($id);
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
            $photo = $this->photoService->getPhotoWithError( $id);
            if ($photo === null) {
                $this->error('Photo not found');
            }

            $name = $photo->getOriginalFilename();
            $this->curatorService->deleteNotImportedPhoto($photo);
            $this->flashMessage('Photo ' . $name . ' deleted.', 'success');
        } catch (\Throwable $exception) {
            $this->flashMessage('An error occurred: ' . $exception->getMessage(), 'danger');
        }

        $this->redirect(':upload');
    }

    public function specimenIdFormSucceeded(Form $form, \stdClass $values): void
    {
        try {
            $photo = $this->photoService->getPhotoWithError((int) $values->photoId);
            if ($photo === null) {
                $this->error('Photo not found');
            }

            $this->curatorService->reimportPhoto($this->photoService->getPhotoReference($values->photoId), (string) $values->specimen);

            $fullID = $this->herbarium->getAcronym() . '-' . $values->specimen;
            $this->flashMessage('File successfully marked to be re-processed with ID ' . $fullID, 'success');
        } catch (\Throwable $exception) {
            $this->flashMessage('An error occurred: ' . $exception->getMessage(), 'danger');
        }

        $this->redirect(':upload');
    }

    protected function createComponentSpecimenIdForm(): Form
    {
        $form = $this->formFactory->create();
        $form->addInteger('specimen', 'ID:')
            ->setRequired('Please insert only number.')
            ->addRule($form::Integer, 'It must be integer');
        $form->addHidden('photoId', $this->photo->getId());
        $form->addSubmit('submit', 'Import with this ID');
        $form->onSuccess[] = [$this, 'specimenIdFormSucceeded'];

        return $form;
    }

}
