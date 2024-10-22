<?php declare(strict_types = 1);

namespace App\UI\Front\Repository;

use App\Exceptions\SpecimenIdException;
use App\Model\SpecimenFactory;
use App\Services\EntityServices\PhotoService;
use App\Services\RepositoryConfiguration;
use App\Services\S3Service;
use App\UI\Base\UnsecuredPresenter;
use Nette\Application\Responses\CallbackResponse;
use Nette\Http\IRequest;
use Nette\Http\Response;

final class RepositoryPresenter extends UnsecuredPresenter
{

    /** @inject */ public S3Service $s3Service;

    /** @inject */ public SpecimenFactory $specimenFactory;

    /** @inject */ public PhotoService $photoService;

    /** @inject */ public RepositoryConfiguration $repositoryConfiguration;

    public function renderArchiveImage(int $id): void
    {
        $photo = $this->photoService->getPublicPhoto($id);
        if ($photo === null) {
            $this->error('The requested photo does not exists.');
        }

        $bucket = $this->repositoryConfiguration->getArchiveBucket();
        $filename = $photo->getArchiveFilename();
        if ($this->s3Service->objectExists($bucket, $filename)) {
            $head = $this->s3Service->headObject($bucket, $filename);
            $stream = $this->s3Service->getStreamOfObject($bucket, $filename);

            $callback = function (IRequest $httpRequest, Response $httpResponse) use ($filename, $head, $stream): void {
                $httpResponse->setHeader('Content-Type', $head['ContentType']);
                $httpResponse->setHeader('Content-Disposition', 'inline; filename' . $filename);
                fpassthru($stream);
                fclose($stream);
            };

            $response = new CallbackResponse($callback);
            $this->sendResponse($response);
        } else {
            $this->error('The requested image does not exists.');
        }
    }

    public function renderSpecimen(?string $specimenFullId): void
    {
        try {
            if ($specimenFullId === null) {
                throw new SpecimenIdException();
            }

            $specimen = $this->specimenFactory->create($specimenFullId);
        } catch (SpecimenIdException $exception) {
            $this->flashMessage($exception->getMessage(), 'error');
            $this->redirect('Home:');
        }

        if (!$this->photoService->specimenHasPublicPhotos($specimen)) {
            $this->error('Specimen ' . $specimenFullId . ' not in evidence.');
        }

        $this->template->id = $specimenFullId;
        $this->template->images = $this->photoService->getPublicPhotosOfSpecimen($specimen);

        $this->template->manifestAbsoluteLink = $this->link('//Iiif:manifest', $specimenFullId);
    }

}
