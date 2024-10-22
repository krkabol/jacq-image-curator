<?php declare(strict_types = 1);

namespace App\UI\Front\Sign;

use App\UI\Base\BasePresenter;
use App\UI\Base\UnsecuredPresenter;
use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;

final class SignPresenter extends UnsecuredPresenter
{

    /**
     * @persistent
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint
     */
    public $backlink;

    public function actionIn(): void
    {
        if ($this->getUser()->isLoggedIn()) {
            $this->redirect(BasePresenter::DESTINATION_AFTER_SIGN_IN);
        }
    }

    public function actionOut(): void
    {
        if ($this->getUser()->isLoggedIn()) {
            $this->getUser()->logout();
        }

        $this->redirect(BasePresenter::DESTINATION_AFTER_SIGN_OUT);
    }

    public function processLoginForm(Form $form): void
    {
        try {
            $this->getUser()->setExpiration($form->values->remember ? '14 days' : '20 minutes');
            $this->getUser()->login($form->values->username, $form->values->password);
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
