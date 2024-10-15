<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;

#[Entity()]
#[Table(name: 'photos_status', options: ['comment' => 'List of allowed photo statuses'])]
class PhotosStatus
{

    use TId;

    public const int WAITING = 1;
    public const int CONTROL_ERROR = 2;
    public const int CONTROL_OK = 3;
    public const int PUBLIC = 4;
    public const int HIDDEN = 5;
    public const array PASSED = [self::CONTROL_OK, self::PUBLIC, self::HIDDEN];
    public const array PASSED_PUBLIC = [self::CONTROL_OK, self::PUBLIC];

    #[Column(unique: true, nullable: false, options: ['comment' => 'name of the status'])]
    protected string $name;

    #[Column(unique: true, nullable: false, options: ['comment' => 'short description'])]
    protected string $description;

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

}
