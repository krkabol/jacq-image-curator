<?php

declare(strict_types=1);

namespace app\UI\Report;

use App\Services\ReportService;
use app\Services\S3Service;
use app\Services\StorageConfiguration;
use app\UI\Base\SecuredPresenter;


final class ReportPresenter extends SecuredPresenter
{
    /** @inject  */
    public ReportService $reportService;
    /** @inject */
    public S3Service $s3Service;
    /** @inject */
    public StorageConfiguration $configuration;

    public function renderIntegrity()
    {
        $this->s3Service->bucketsExists($this->configuration->getAllBuckets()) ? $this->template->bucketsOK = true : $this->template->bucketsOK = false;
        $this->template->dbRecordsMissingWithinArchive = $this->reportService->dbRecordsMissingWithinArchive();
        $this->template->dbRecordsMissingWithinIIIF = $this->reportService->dbRecordsMissingWithinIIIF();
        $this->template->unprocessedNewFiles = $this->reportService->unprocessedNewFiles();
        $this->template->TIFFsWithoutJP2 = $this->reportService->TIFFsWithoutJP2();
        $this->template->JP2sWithoutTIFF = $this->reportService->JP2sWithoutTIFF();
        $this->template->TIFFsWithoutDbRecord = $this->reportService->TIFFsWithoutDbRecord();

    }
}
