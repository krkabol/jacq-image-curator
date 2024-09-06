<?php

declare(strict_types=1);

namespace app\Services;

use app\Model\PhotoOfSpecimenFactory;
use app\Model\ImportStages\BarcodeStage;
use app\Model\ImportStages\ImportStageException;
use app\Model\ImportStages\StageFactory;
use League\Pipeline\Pipeline;
use Nette\Neon\Exception;

class ImageService
{
    const int LIMIT = 100;
    public function __construct(protected readonly S3Service $S3Service, protected readonly PhotoOfSpecimenFactory $photoOfSpecimenFactory, protected readonly StageFactory $stageFactory, protected readonly StorageConfiguration $storageConfiguration)
    {
    }

    public function proceedNewImages(): array
    {        throw new Exception("not tested in production !");

        return $this->runPipeline($this->fileProcessingPipeline());
    }

    public function fileProcessingPipeline(): Pipeline
    {
        return $this->controlPipeline()
            ->pipe($this->stageFactory->createConvertStage())
            ->pipe($this->stageFactory->createArchiveStage())
            ->pipe($this->stageFactory->createRegisterStage())
            ->pipe($this->stageFactory->createCleanupStage());
    }

    public function controlPipeline(): Pipeline
    {
        return (new Pipeline())
            ->pipe($this->stageFactory->createFilenameControlStage())
            ->pipe($this->stageFactory->createNoveltyControlStage())
            ->pipe($this->stageFactory->createDimensionsStage())
            ->pipe(new BarcodeStage);
    }

    public function proceedDryrun(): array
    {
        return $this->runPipeline($this->controlPipeline());
    }

    protected function runPipeline(Pipeline $pipeline): array
    {
        $success = [];
        $error = [];
        $i = 0;
        foreach ($this->S3Service->listObjectsNamesOnly($this->storageConfiguration->getCuratorBucket()) as $file) {
            try {
                $photo = $this->photoOfSpecimenFactory->create($this->storageConfiguration->getCuratorBucket(), $file);
                $pipeline->process($photo);
                $success[$file] = "OK";
                $i++;
            } catch (ImportStageException $e) {
                $error[$file] = $e->getMessage();
            }
            if ($i >= self::LIMIT) {
                break;
            }
        }
        return [$success, $error];
    }


}
