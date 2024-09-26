<?php declare(strict_types = 1);

namespace App\UI\Base;

use App\Services\AppConfiguration;
use App\UI\Base\Form\FormFactory;
use Nette\Application\UI\Presenter;

abstract class BasePresenter extends Presenter
{

    public const string DESTINATION_AFTER_SIGN_IN = ':Admin:Home:';
    public const string DESTINATION_AFTER_SIGN_OUT = ':Front:Home:';
    public const string DESTINATION_LOG_IN = ':Front:Sign:in';

    /** @inject */ public AppConfiguration $appConfiguration;

    /** @inject */ public FormFactory $formFactory;

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
        //# //TODO https://forum.nette.org/en/36020-nette-v3-redirect-http-to-https  + https://doc.nette.org/en/http/configuration#toc-http-proxy
        $baseUrl = $this->getHttpRequest()->getUrl()->getBaseUrl();

        return preg_replace('/^http:/', 'https:', $baseUrl);
    }

}
