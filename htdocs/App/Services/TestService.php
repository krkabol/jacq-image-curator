<?php declare(strict_types = 1);

namespace App\Services;

use App\Model\Database\EntityManager;
use App\Model\MigrationStages\StageFactory;
use App\Model\PhotoOfSpecimenFactory;
use League\Pipeline\Pipeline;
use Nette\Neon\Exception;

class TestService
{

    public const int LIMIT = 10;

    public function __construct(protected readonly WebDir $webDir, protected readonly S3Service $S3Service, protected readonly PhotoOfSpecimenFactory $photoOfSpecimenFactory, protected readonly StageFactory $stageFactory, protected readonly StorageConfiguration $storageConfiguration, protected readonly EntityManager $entityManager)
    {
    }

    /** @deprecated
     * used during initial stage for MinIO temp repository
     */
    public function initialize(): void
    {
        throw new Exception('do not initialize repository!');
    }

    public function migrationPipeline(): Pipeline
    {
        return (new Pipeline())
            ->pipe($this->stageFactory->createJp2ExistsStage())
            ->pipe($this->stageFactory->createFilenameControlStage())
            ->pipe($this->stageFactory->createDimensionsStage())
            ->pipe($this->stageFactory->createBarcodeStage())
            ->pipe($this->stageFactory->createUpdateRecordStage());
    }

}
