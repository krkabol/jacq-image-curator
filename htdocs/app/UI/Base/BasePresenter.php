<?php

declare(strict_types=1);

namespace app\UI\Base;

use App\Services\AppConfiguration;
use Nette\Application\UI\Presenter;


abstract class BasePresenter extends Presenter
{
    const DESTINATION_AFTER_SIGN_IN = "Curator:";
    const DESTINATION_AFTER_SIGN_OUT = "Home:";
    /** @inject */
    public AppConfiguration $appConfiguration;

    protected function beforeRender()
    {
        if ($this->appConfiguration->getPlatform() !== NULL) {
            $this->template->platform = $this->appConfiguration->getPlatform();
        }
        parent::beforeRender();
    }
}
