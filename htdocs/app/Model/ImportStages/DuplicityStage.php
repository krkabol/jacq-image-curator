<?php

declare(strict_types=1);

namespace App\Model\ImportStages;

use App\Model\Database\Entity\Photos;
use App\Model\Database\Entity\PhotosStatus;
use App\Model\Database\EntityManager;
use League\Pipeline\StageInterface;
use Nette\Application\LinkGenerator;

class DuplicityStageException extends ImportStageException
{

}

class DuplicityStage implements StageInterface
{

    public function __construct(protected readonly EntityManager $entityManager, protected readonly LinkGenerator $linkGenerator)
    {
    }


    public function __invoke($payload)
    {
        //TODO - preselect those with correct status - not only OK !!
        /** @var Photos $payload */
        $duplicity = $this->entityManager->getPhotosRepository()->findOneBy(["specimenId" => $payload->getSpecimenId(), "archiveFileSize" => $payload->getArchiveFileSize(), "status" => [PhotosStatus::CONTROL_OK]]);
        if ($duplicity !== NULL) {
            /** @var Photos $duplicity */
            $link = $this->linkGenerator->link("//:Front:Repository:specimen", [$duplicity->getFullSpecimenId()]);
            throw new DuplicityStageException("suspicious similarity with file " . $duplicity->getArchiveFilename(). " already imported to the specimen <a href=\"".$link."\">".$payload->getFullSpecimenId()."</a>");
        }
        return $payload;
    }
}
