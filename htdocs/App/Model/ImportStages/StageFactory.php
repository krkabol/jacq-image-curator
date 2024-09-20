<?php

declare(strict_types=1);

namespace App\Model\ImportStages;

use App\Model\Database\EntityManager;
use App\Services\ImageService;
use App\Services\S3Service;
use App\Services\StorageConfiguration;
use App\Services\TempDir;
use Nette\Application\LinkGenerator;

readonly class StageFactory
{

    public function __construct(protected S3Service $s3Service, protected TempDir $tempDir, protected EntityManager $entityManager, protected StorageConfiguration $storageConfiguration, protected ImageService $imageService, protected LinkGenerator $linkGenerator)
    {
    }

    public function createDownloadStage(): DownloadStage
    {
        return new DownloadStage($this->s3Service, $this->storageConfiguration);
    }

    public function createBarcodeStage(): BarcodeStage
    {
        return new BarcodeStage($this->storageConfiguration, $this->imageService);
    }

    public function createDimensionsStage(): DimensionsStage
    {
        return new DimensionsStage($this->storageConfiguration, $this->imageService);
    }

    public function createThumbnailStage(): ThumbnailStage
    {
        return new ThumbnailStage($this->storageConfiguration, $this->imageService);
    }

    public function createConvertStage(): ConvertStage
    {
        return new ConvertStage($this->s3Service, $this->storageConfiguration);
    }

    public function createDuplicityStage(): DuplicityStage
    {
        return new DuplicityStage($this->entityManager, $this->linkGenerator);
    }

    public function createTransferStage(): TransferStage
    {
        return new TransferStage($this->s3Service, $this->storageConfiguration);
    }
}
