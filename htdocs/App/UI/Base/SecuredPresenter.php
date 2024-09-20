<?php declare(strict_types = 1);

namespace App\UI\Base;

use App\Model\Database\Entity\Herbaria;
use App\Model\Database\EntityManager;
use App\UI\Base\Form\FormFactory;

abstract class SecuredPresenter extends BasePresenter
{

    /** @inject */ public FormFactory $formFactory;

    /** @inject */ public EntityManager $entityManager;

    protected Herbaria $herbarium;

    public function checkRequirements(\ReflectionClass|\ReflectionMethod $element): void
    {
        if (!$this->user->isLoggedIn()) {
            $this->redirect(
                BasePresenter::DESTINATION_LOG_IN,
                ['backlink' => $this->storeRequest()]
            );
        }

        parent::checkRequirements($element);
    }

    public function startup(): void
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
