<?php declare(strict_types=1);

namespace App\Model\Database\Entity\Attributes;

use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait TLastEditAt
{

    #[ORM\Column(name: "lastedit_timestamp", type: Types::DATETIME_MUTABLE, nullable: false)]
    protected DateTime $lastEdit;

    public function getLastEditAt(): DateTime
    {
        return $this->lastEdit;
    }

    #[ORM\PreUpdate()]
    public function setLastEditAt()
    {
        $this->lastEdit = new DateTime();
        return $this;
    }

}