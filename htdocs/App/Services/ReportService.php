<?php declare(strict_types = 1);

namespace App\Services;

use App\Model\Database\Entity\Photos;
use App\Model\Database\EntityManager;

/**
 * rubbish at this moment
 */
final class ReportService
{

    protected $photosRepository;

    protected $dbRecords;

    public function __construct(protected readonly S3Service $S3Service, protected readonly RepositoryConfiguration $storageConfiguration, protected readonly EntityManager $entityManager)
    {
        $this->photosRepository = $entityManager->getPhotosRepository();
    }



}
