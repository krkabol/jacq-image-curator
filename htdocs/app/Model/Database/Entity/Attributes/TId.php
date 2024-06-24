<?php declare(strict_types=1);

namespace app\Model\Database\Entity\Attributes;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait TId
{

    #[ORM\Column(type: Types::INTEGER, unique: true, nullable: false)]
    #[ORM\Id, ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    public function getId(): int
    {
        return $this->id;
    }

    public function __clone()
    {
        $this->id = null;
    }
}
