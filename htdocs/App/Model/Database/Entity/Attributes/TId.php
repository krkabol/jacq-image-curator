<?php declare(strict_types = 1);

namespace App\Model\Database\Entity\Attributes;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;

trait TId
{

    #[Column(type: Types::INTEGER, unique: true, nullable: false)]
    #[Id, GeneratedValue(strategy: 'IDENTITY')]
    protected ?int $id;

    public function getId(): int
    {
        return $this->id;
    }

    public function __clone()
    {
        $this->id = null;
    }

}
