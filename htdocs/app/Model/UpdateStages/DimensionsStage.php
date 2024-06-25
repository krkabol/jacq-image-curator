<?php

declare(strict_types=1);

namespace app\Model\UpdateStages;

use app\Model\Database\Entity\Photos;
use app\Services\TempDir;
use Exception;
use Imagick;
use League\Pipeline\StageInterface;


class DimensionStageException extends BaseStageException
{

}

class DimensionsStage implements StageInterface
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
            $imagick = new Imagick($this->tempDir->getPath($payload->getJp2Filename()));
            $payload->setWidth($imagick->getImageWidth());
            $payload->setHeight($imagick->getImageHeight());
            unset($imagick);
        } catch (Exception $exception) {
            throw new DimensionStageException("dimensions error (" . $exception->getMessage() . ")");
        }
        return $payload;
    }
}
