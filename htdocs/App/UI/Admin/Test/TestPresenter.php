<?php

declare(strict_types=1);

namespace App\UI\Admin\Test;

use App\Services\S3Service;
use App\Services\StorageConfiguration;
use App\Services\TestService;
use App\UI\Base\BasePresenter;
use App\UI\Base\SecuredPresenter;


final class TestPresenter extends SecuredPresenter
{
    public const TEST_FILES = ["prc_407087.tif", "prc_407135.tif"];

    /** @inject */
    public S3Service $s3Service;

    /** @inject */
    public StorageConfiguration $configuration;

    /** @inject */
    public TestService $testService;

   public function checkRequirements($element): void
   {
       if($this->user->getId()!=="admin" ||  getenv('NETTE_ENV', true) == "production"){
           $this->redirect(BasePresenter::DESTINATION_AFTER_SIGN_IN);
       }
       parent::checkRequirements($element);
   }

    public function renderDefault()
    {

        $this->template->buckets = $this->s3Service->listBuckets();
    }

    public function actionInitialize()
    {
        $this->testService->initialize();
        $this->redirect(":default");
    }

    public function renderProceed()
    {
        $result = $this->testService->proceedNewImages();
        $this->template->success = $result[0];
        $this->template->error = $result[1];
    }

}
