<?php

declare(strict_types=1);

namespace app\UI\Curator;

use app\Services\ImageService;
use app\UI\Base\SecuredPresenter;


final class CuratorPresenter extends SecuredPresenter
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
