<?php

declare(strict_types=1);

namespace app\Model\Stages;

use app\Model\PhotoOfSpecimen;
use League\Pipeline\StageInterface;

class FilenameControlException extends BaseStageException
{

}

class FilenameControlStage implements StageInterface
{
    const NAME_TEMPLATE = '/^(?P<herbarium>[a-zA-Z]+)_(?P<specimenId>\d+)(?P<supplement>[_\-a-zA-Z]*)\.(?P<extension>tif)$/';
    protected $herbariaAvailable;
    protected PhotoOfSpecimen $item;

    public function __construct($herbariaAvailable)
    {
        $this->herbariaAvailable = $herbariaAvailable;
    }

    public function __invoke($payload)
    {
        $this->item = $payload;
        $this->splitName();
        $this->checkAcronymExists();
        $this->checkSpecimenExists();
        return $this->item;
    }

    protected function splitName(): void
    {
        $parts = [];
        if (preg_match(self::NAME_TEMPLATE, $this->item->getObjectKey(), $parts)) {
            $this->item->setHerbariumAcronym($parts['herbarium']);
            $this->item->setSpecimenId($parts['specimenId']);
        } else {
            throw new FilenameControlException("invalid name format: " . $this->item->getObjectKey());
        }
    }

    protected function checkAcronymExists(): void
    {
        if (!in_array(strtoupper($this->item->getHerbariumAcronym()), $this->herbariaAvailable)) {
            throw new FilenameControlException("invalid herbarium acronym: " . $this->item->getHerbariumAcronym());
        }
    }

    protected function checkSpecimenExists(): void
    {
        // TODO - will we ask JACQ API? - because it is possible to have a specimen with photo not yet included in JACQ I expect..
    }

}
