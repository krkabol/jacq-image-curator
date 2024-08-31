<?php

declare(strict_types=1);

namespace App\UI\Admin\Home;

use app\Services\ImageService;
use app\UI\Base\SecuredPresenter;


final class HomePresenter extends SecuredPresenter
{
    /** @inject */
    public ImageService $imageService;

    public function renderDryrun()
    {
        $result = $this->imageService->proceedDryrun();
        $this->setView("proceed");
        $this->template->success = $result[0];
        $this->template->error = $result[1];
    }


}
