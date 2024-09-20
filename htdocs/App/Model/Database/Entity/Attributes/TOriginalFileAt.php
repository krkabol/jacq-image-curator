<?php declare(strict_types=1);

namespace App\Model\Database\Entity\Attributes;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait TOriginalFileAt
{

    #[ORM\Column(name: "original_file_timestamp", type: Types::DATETIME_IMMUTABLE, nullable: true, options: ["comment" => "Timestamp of original file creation"])]
    protected DateTimeImmutable $originalFileTimestamp;

    public function getOriginalFileAt()
    {
        return $this->originalFileTimestamp;
    }

    public function setOriginalFileAt($timestamp)
    {
        $this->originalFileTimestamp = $timestamp;
        return $this;
    }

}
