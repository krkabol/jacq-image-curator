<?php declare(strict_types = 1);

namespace App\UI\Admin\Report;

use App\Services\ReportService;
use App\Services\S3Service;
use App\Services\RepositoryConfiguration;
use App\UI\Base\SecuredPresenter;

final class ReportPresenter extends SecuredPresenter
{

    /** @inject */
    public ReportService $reportService;

    /** @inject */
    public RepositoryConfiguration $repositoryConfiguration;


}
