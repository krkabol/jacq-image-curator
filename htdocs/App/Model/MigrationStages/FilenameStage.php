<?php declare(strict_types = 1);

namespace App\Model\MigrationStages;

use App\Model\Database\Entity\Herbaria;
use App\Model\Database\Entity\Photos;
use App\Model\Database\EntityManager;
use App\Model\MigrationStages\Exceptions\FilenameException;
use App\Services\StorageConfiguration;
use League\Pipeline\StageInterface;

class FilenameStage implements StageInterface
{

    protected EntityManager $entityManager;

    protected StorageConfiguration $configuration;

    protected Photos $item;

    public function __construct(EntityManager $entityManager, StorageConfiguration $configuration)
    {
        $this->entityManager = $entityManager;
        $this->configuration = $configuration;
    }

    protected function splitName(): void
    {
        $parts = [];
        if (preg_match($this->configuration->getPhotoNameRegex(), $this->item->getArchiveFilename(), $parts)) {
            $this->item->setHerbarium($this->findHerbarium($parts['herbarium']));
            $this->item->setSpecimenId($parts['specimenId']);
        } else {
            throw new FilenameException('invalid name format');
        }
    }

    protected function findHerbarium(string $acronym): Herbaria
    {
        $herbarium = $this->entityManager->getHerbariaRepository()->findOneWithAcronym($acronym);
        if ($herbarium === null) {
            throw new FilenameException('unknown herbarium: ' . $acronym);
        }

        return $herbarium;
    }

    public function __invoke(Photos $payload): Photos
    {
        $this->item = $payload;
        $this->splitName();

        return $this->item;
    }

}
