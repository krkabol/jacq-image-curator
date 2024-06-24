<?php

declare(strict_types=1);

namespace app\UI\Base;
use Nette\Application\UI\Presenter;
use Nette\Security\User;


abstract class SecuredPresenter extends Presenter
{
    public function checkRequirements($element): void
    {
        if (!$this->user->isLoggedIn()) {
            if ($this->user->getLogoutReason() === User::LogoutInactivity) {
            }

            $this->redirect(
                "Sign:in",
                ['backlink' => $this->storeRequest()]
            );
        }
    }

}
