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
        if ($this->appConfiguration->getPlatform() !== 'production') {
            $this->template->platform = $this->appConfiguration->getPlatform();
        }

        $this->template->version = $this->appConfiguration->getVersion();

        parent::beforeRender();
    }

}
