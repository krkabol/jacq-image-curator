<?php

declare(strict_types=1);

namespace app\Model\ImportStages;

use app\Model\Database\Entity\Photos;
use App\Model\Database\EntityManager;
use League\Pipeline\StageInterface;

class DuplicityStageException extends ImportStageException
{

}

class DuplicityStage implements StageInterface
{

    public function __construct(protected readonly EntityManager $entityManager)
    {
    }


    public function __invoke($payload)
    {
        /** @var Photos $payload */
        $duplicity = $this->entityManager->getPhotosRepository()->findOneBy(["specimenId" => $payload->getSpecimenId(), "archiveFileSize" => $payload->getArchiveFileSize()]);
        if ($duplicity !== NULL) {
            throw new DuplicityStageException("suspicious similarity with file " . $duplicity->getArchiveFilename());
        }
        return $payload;
    }
}
