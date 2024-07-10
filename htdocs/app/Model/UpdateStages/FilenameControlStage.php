<?php

declare(strict_types=1);

namespace app\Model\UpdateStages;

use app\Model\Database\Entity\Herbaria;
use app\Model\Database\Entity\Photos;
use App\Model\Database\EntityManager;
use app\Services\StorageConfiguration;
use League\Pipeline\StageInterface;


class FilenameControlException extends BaseStageException
{

}

class FilenameControlStage implements StageInterface
{
    protected EntityManager $entityManager;
    protected StorageConfiguration $configuration;
    protected Photos $item;

    public function __construct(EntityManager $entityManager, StorageConfiguration $configuration)
    {
        $this->entityManager = $entityManager;
        $this->configuration = $configuration;
    }

    public function __invoke($payload)
    {
        $this->item = $payload;
        $this->splitName();
        return $this->item;
    }

    protected function splitName(): void
    {
        $parts = [];
        if (preg_match($this->configuration->getPhotoNameRegex(), $this->item->getArchiveFilename(), $parts)) {
            $this->item->setHerbarium($this->findHerbarium($parts['herbarium']));
            $this->item->setSpecimenId($parts['specimenId']);
        } else {
            throw new FilenameControlException("invalid name format");
        }
    }

    protected function findHerbarium(string $acronym): Herbaria
    {
        return $this->entityManager->getHerbariaRepository()->findOneByAcronym($acronym);
    }

}
