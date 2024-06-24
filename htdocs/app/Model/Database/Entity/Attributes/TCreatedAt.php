<?php declare(strict_types=1);

namespace app\Model\Database\Entity\Attributes;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait TCreatedAt
{

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: false)]
    protected \DateTimeImmutable $createdAt;

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt()
    {
        $this->createdAt = new \DateTimeImmutable();
        return $this;
    }

}
