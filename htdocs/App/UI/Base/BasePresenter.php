<?php

declare(strict_types=1);

namespace App\UI\Base;

use App\Services\AppConfiguration;
use Nette\Application\UI\Presenter;


abstract class BasePresenter extends Presenter
{
    const string DESTINATION_AFTER_SIGN_IN = ":Admin:Home:";
    const string DESTINATION_AFTER_SIGN_OUT = ":Front:Home:";
    const string DESTINATION_LOG_IN = ":Front:Sign:in";
    /** @inject */
    public AppConfiguration $appConfiguration;

    protected function beforeRender()
    {
        if ($this->appConfiguration->getPlatform() !== NULL) {
            $this->template->platform = $this->appConfiguration->getPlatform();
        }
        parent::beforeRender();
    }

    /**
     * we are running http behind the proxy
     */
    protected function getAbsoluteHttpsBasePath()
    {
        $baseUrl = $this->getHttpRequest()->getUrl()->getBaseUrl();
        return preg_replace('/^http:/', 'https:', $baseUrl);
    }
}
