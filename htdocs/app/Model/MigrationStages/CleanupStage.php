<?php

declare(strict_types=1);

namespace app\Model\MigrationStages;


use app\Model\Database\Entity\Photos;
use app\Services\TempDir;
use Exception;
use League\Pipeline\StageInterface;


class CleanupStageException extends BaseStageException
{

}
/** @deprecated  */
class CleanupStage implements StageInterface
{
    protected TempDir $tempDir;

    public function __construct(TempDir $tempDir)
    {
        $this->tempDir = $tempDir;
    }

    public function __invoke($payload)
    {
        try {
            /** @var Photos $payload */
            unlink($this->tempDir->getPath($payload->getJp2Filename()));
        } catch (Exception $exception) {
            throw new CleanupStageException("cleanup error (" . $exception->getMessage() . ")");
        }
        return $payload;
    }

}