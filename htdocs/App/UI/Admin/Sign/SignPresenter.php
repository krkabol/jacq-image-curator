<?php declare(strict_types=1);

namespace App\UI\Admin\Sign;

use App\UI\Base\BasePresenter;
use App\UI\Base\Form\FormFactory;
use App\UI\Base\SecuredPresenter;
use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;

final class SignPresenter extends SecuredPresenter
{

    /** @var string @persistent */
    public $backlink;


    public function checkRequirements($element): void
    {
    }

    public function actionIn(): void
    {
          if ($this->user->isLoggedIn()) {
            $this->redirect(BasePresenter::DESTINATION_AFTER_SIGN_IN);
        }
    }

    public function actionOut(): void
    {
        if ($this->user->isLoggedIn()) {
            $this->user->logout();
        }

        $this->redirect(BasePresenter::DESTINATION_AFTER_SIGN_OUT);
    }

    public function processLoginForm(Form $form): void
    {
        try {
            $this->user->setExpiration($form->values->remember ? '14 days' : '20 minutes');
            $this->user->login($form->values->username, $form->values->password);
        } catch (AuthenticationException $e) {
            $form->addError('Invalid credentials');

            return;
        }
        if ($this->backlink !== null) {

            $this->restoreRequest($this->backlink);
        }
        $this->redirect(BasePresenter::DESTINATION_AFTER_SIGN_IN);
    }

    protected function createComponentLoginForm(): Form
    {
        $form = $this->formFactory->create();
        $form->addText('username')
            ->setRequired(true);
        $form->addPassword('password')
            ->setRequired(true);
        $form->addCheckbox('remember')
            ->setDefaultValue(true);
        $form->addSubmit('submit');
        $form->onSuccess[] = [$this, 'processLoginForm'];

        return $form;
    }

}
