<?php declare(strict_types = 1);

namespace App\Model\ImportStages;

use App\Model\Database\Entity\PhotosStatus;
use App\Model\Database\EntityManager;
use App\Model\ImportStages\Exceptions\DuplicityStageException;
use League\Pipeline\StageInterface;
use Nette\Application\LinkGenerator;

readonly class DuplicityStage implements StageInterface
{

    public function __construct(protected EntityManager $entityManager, protected LinkGenerator $linkGenerator)
    {
    }

    public function __invoke(mixed $payload): mixed
    {
        //TODO - preselect those with correct status - not only OK !!
        $duplicity = $this->entityManager->getPhotosRepository()->findOneBy(['specimenId' => $payload->getSpecimenId(), 'archiveFileSize' => $payload->getArchiveFileSize(), 'status' => [PhotosStatus::CONTROL_OK]]);
        if ($duplicity !== null) {
            $link = $this->linkGenerator->link(':Front:Repository:specimen', [$duplicity->getFullSpecimenId()], null, 'link');

            throw new DuplicityStageException('suspicious similarity with file ' . $duplicity->getArchiveFilename() . ' already imported to the specimen <a href="' . $link . '">' . $payload->getFullSpecimenId() . '</a>');
        }

        return $payload;
    }

}
