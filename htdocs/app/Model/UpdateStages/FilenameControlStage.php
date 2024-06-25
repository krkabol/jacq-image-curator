<?php

declare(strict_types=1);

namespace app\Model\UpdateStages;

use app\Model\Database\Entity\Herbaria;
use app\Model\Database\Entity\Photos;
use App\Model\Database\EntityManager;
use League\Pipeline\StageInterface;


class FilenameControlException extends BaseStageException
{

}

class FilenameControlStage implements StageInterface
{
    const NAME_TEMPLATE = '/^(?P<herbarium>[a-zA-Z]+)_(?P<specimenId>\d+)(?P<supplement>[_\-a-zA-Z]*)\.(?P<extension>tif)$/';
    protected EntityManager $entityManager;
    protected Photos $item;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
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
        if (preg_match(self::NAME_TEMPLATE, $this->item->getArchiveFilename(), $parts)) {
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
