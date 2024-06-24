<?php

declare(strict_types=1);

namespace app\Model\Stages;

use App\Model\Database\EntityManager;
use League\Pipeline\StageInterface;

class NotInDatabaseStageException extends BaseStageException
{

}

class NotInDatabaseStage implements StageInterface
{

    protected EntityManager $entityManager;


    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke($payload)
    {
        if ($this->entityManager->getPhotosRepository()->findOneByArchiveFilename($payload->getObjectKey()) !== null) {
            throw new NotInDatabaseStageException("already registred file: " . $payload->getObjectKey());
        }

        return $payload;
    }

}
