<?php declare(strict_types=1);

namespace App\UI\Front\Home;

use App\Services\EntityServices\HerbariumService;
use App\UI\Base\UnsecuredPresenter;

final class HomePresenter extends UnsecuredPresenter
{
    /** @inject */
    public HerbariumService $herbariumService;

    public function renderContact()
    {
        $this->template->mainContact = $this->herbariumService->findOneBy(["acronym" => "PRC"]);
        $this->template->herbaria = $this->herbariumService->findAll();
    }
}
