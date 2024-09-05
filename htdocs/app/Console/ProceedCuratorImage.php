<?php declare(strict_types=1);

namespace App\Console;

use app\Model\Database\Entity\Photos;
use app\Model\Database\Entity\PhotosStatus;
use App\Model\Database\EntityManager;
use app\Services\S3Service;
use app\Services\StorageConfiguration;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProceedCuratorImage extends Command
{

    public function __construct(protected readonly EntityManager $entityManager, protected readonly StorageConfiguration $storageConfiguration, protected readonly S3Service $s3Service, ?string $name = null)
    {
        parent::__construct($name);
    }

    public function getPhoto(): ?Photos
    {
        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata('app\Model\Database\Entity\Photos', 'p');
        $query = $this->entityManager->createNativeQuery('SELECT p.* FROM photos p WHERE status_id = ? ORDER BY id asc FOR UPDATE SKIP LOCKED  LIMIT 1 ', $rsm);
        $query->setParameter(1, PhotosStatus::WAITING);
        /** @var Photos $photo */
        $photo = $query->getSingleResult();
        return $photo;
    }

    protected function configure(): void
    {
        $this->setName('curator:importImage');
        $this->setDescription('take an image from curator bucket and prepare necessary files');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->entityManager->getConnection()->beginTransaction();
        try {
            $photo = $this->getPhoto();
            $output->write("\n filename:" .$photo->getHerbarium()->getAcronym(). $photo->getOriginalFilename() . "\n");
            $this->entityManager->flush();
            $this->entityManager->getConnection()->commit();
        } catch (Exception $e) {
            $this->entityManager->getConnection()->rollBack();
            $output->write("\n ERROR: " . $e->getMessage() . "\n");
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }

}
