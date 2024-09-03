<?php declare(strict_types=1);

namespace App\Services;

use App\Model\Database\EntityManager;
use App\Model\FileManagement\File;

readonly class CuratorService
{

    public function __construct(protected readonly EntityManager $entityManager, protected readonly S3Service $s3Service)
    {
    }

    public function getAvailableNewFiles($herbariumId): array
    {
        $herbarium = $this->entityManager->getHerbariaRepository()->find($herbariumId);
        $files = [];
        foreach ($this->s3Service->listObjects($herbarium->getBucket()) as $filename) {
             $file = new File($filename,$this->s3Service->headObject($herbarium->getBucket(), $filename));

            $files[] = $file;

        }
        return $files;
    }

}
