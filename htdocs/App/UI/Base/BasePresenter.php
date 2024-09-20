<?php declare(strict_types = 1);

namespace App\UI\Base;

use App\Services\AppConfiguration;
use Nette\Application\UI\Presenter;

abstract class BasePresenter extends Presenter
{

    public const string DESTINATION_AFTER_SIGN_IN = ':Admin:Home:';
    public const string DESTINATION_AFTER_SIGN_OUT = ':Front:Home:';
    public const string DESTINATION_LOG_IN = ':Front:Sign:in';

    /** @inject */
    public AppConfiguration $appConfiguration;

    protected function beforeRender(): void
    {
        if ($this->appConfiguration->getPlatform() !== null) {
            $this->template->platform = $this->appConfiguration->getPlatform();
        }

        parent::beforeRender();
    }

    /**
     * we are running http behind the proxy
     */
    protected function getAbsoluteHttpsBasePath(): string
    {
        $baseUrl = $this->getHttpRequest()->getUrl()->getBaseUrl();

        return preg_replace('/^http:/', 'https:', $baseUrl);
    }

}
