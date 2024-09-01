<?php

declare(strict_types=1);

namespace App\UI\Admin\Home;

use App\Model\Database\EntityManager;
use app\Services\ImageService;
use app\Services\S3Service;
use app\UI\Base\SecuredPresenter;

final class HomePresenter extends SecuredPresenter
{
    /** @inject */
    public ImageService $imageService;

    /** @inject */
    public S3Service $s3Service;

    /** @inject */
    public EntityManager $entityManager;

    public function renderDefault()
    {
        $herbariumId = $this->getUser()->getIdentity()->herbarium;
        $herbarium = $this->entityManager->getHerbariaRepository()->find($herbariumId);
        $this->template->files = $this->s3Service->listObjects($herbarium->getBucket());
    }

    public function renderDryrun()
    {
        $result = $this->imageService->proceedDryrun();
        $this->setView("proceed");
        $this->template->success = $result[0];
        $this->template->error = $result[1];
    }
}
