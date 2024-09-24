<?php declare(strict_types = 1);

namespace App\UI\Admin\Report;

use App\Services\ReportService;
use App\Services\S3Service;
use App\Services\StorageConfiguration;
use App\UI\Base\SecuredPresenter;

final class ReportPresenter extends SecuredPresenter
{

    /** @inject */
    public ReportService $reportService;

    /** @inject */
    public S3Service $s3Service;

    /** @inject */
    public StorageConfiguration $configuration;

    public function renderIntegrity(): void
    {
        $this->template->dbRecordsMissingWithinArchive = $this->reportService->dbRecordsMissingWithinArchive();
        $this->template->dbRecordsMissingWithinIIIF = $this->reportService->dbRecordsMissingWithinIIIF();
        $this->template->TIFFsWithoutJP2 = $this->reportService->TIFFsWithoutJP2();
        $this->template->JP2sWithoutTIFF = $this->reportService->JP2sWithoutTIFF();
        $this->template->TIFFsWithoutDbRecord = $this->reportService->TIFFsWithoutDbRecord();
    }

}
