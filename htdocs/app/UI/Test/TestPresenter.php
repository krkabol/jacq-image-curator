<?php

declare(strict_types=1);

namespace app\UI\Test;

use app\Services\S3Service;
use app\Services\StorageConfiguration;
use app\Services\TestService;
use app\UI\Base\BasePresenter;
use app\UI\Base\SecuredPresenter;


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
       if($this->user->getId()!=="admin"){
           $this->redirect(BasePresenter::DESTINATION_AFTER_SIGN_IN);
       }
       parent::checkRequirements($element);
   }

    public function renderDefault()
    {
        $this->s3Service->bucketsExists($this->configuration->getAllBuckets()) ? $this->template->bucketsOK = true : $this->template->bucketsOK = false;
        $this->s3Service->objectsExists($this->configuration->getNewBucket(), self::TEST_FILES) ? $this->template->tiffOK = true : $this->template->tiffOK = false;

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

    public function renderProceedMigration()
    {
        $result = $this->testService->proceedExistingImages();
        $this->template->success = $result[0];
        $this->template->error = $result[1];
    }
}
