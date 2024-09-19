<?php

declare(strict_types=1);

namespace app\UI\Base;
use app\Model\Database\Entity\Herbaria;
use Nette\Security\User;


abstract class SecuredPresenter extends BasePresenter
{
    protected Herbaria $herbarium;

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

    public function startup()
    {
        $this->herbarium = $this->entityManager->getReference(Herbaria::class, $this->getUser()->getIdentity()->herbarium);
        parent::startup();
    }

    public function beforeRender(): void
    {
        $this->template->herbarium = $this->herbarium;
        parent::beforeRender();
    }

}
