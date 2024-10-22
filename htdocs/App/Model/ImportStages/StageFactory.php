<?php declare(strict_types = 1);

namespace App\Model\ImportStages;

use App\Model\Database\EntityManager;
use App\Services\AppConfiguration;
use App\Services\EntityServices\PhotoService;
use App\Services\ImageService;
use App\Services\RepositoryConfiguration;
use App\Services\S3Service;
use App\Services\TempDir;
use Nette\Application\LinkGenerator;

readonly class StageFactory
{

    public function __construct(protected S3Service $s3Service, protected TempDir $tempDir, protected EntityManager $entityManager, protected RepositoryConfiguration $repositoryConfiguration, protected ImageService $imageService, protected LinkGenerator $linkGenerator, protected PhotoService $photoService, protected AppConfiguration $appConfiguration)
    {
    }

    public function createDownloadStage(): DownloadStage
    {
        return new DownloadStage($this->s3Service, $this->repositoryConfiguration);
    }

    public function createBarcodeStage(): BarcodeStage
    {
        return new BarcodeStage($this->repositoryConfiguration, $this->imageService);
    }

    public function createMetadataStage(): MetadataStage
    {
        return new MetadataStage($this->repositoryConfiguration, $this->imageService);
    }

    public function createThumbnailStage(): ThumbnailStage
    {
        return new ThumbnailStage($this->repositoryConfiguration, $this->imageService);
    }

    public function createConvertStage(): ConvertStage
    {
        return new ConvertStage($this->s3Service, $this->repositoryConfiguration, $this->imageService);
    }

    public function createDuplicityStage(): DuplicityStage
    {
        return new DuplicityStage($this->photoService, $this->linkGenerator, $this->imageService, $this->repositoryConfiguration, $this->s3Service);
    }

    public function createTransferStage(): TransferStage
    {
        return new TransferStage($this->s3Service, $this->repositoryConfiguration, $this->appConfiguration);
    }

}
