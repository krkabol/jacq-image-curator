<?php

declare(strict_types=1);

namespace app\Services;

use App\Model\Database\EntityManager;


class DatabaseService
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }


}
