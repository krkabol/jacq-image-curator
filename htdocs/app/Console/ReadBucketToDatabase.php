<?php declare(strict_types=1);

namespace App\Console;

use app\Model\Database\Entity\Photos;
use App\Model\Database\EntityManager;
use app\Services\S3Service;
use app\Services\StorageConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReadBucketToDatabase extends Command
{
    protected EntityManager $entityManager;
    protected StorageConfiguration $storageConfiguration;
    protected S3Service $S3Service;

    public function __construct(EntityManager $entityManager, StorageConfiguration $storageConfiguration, S3Service $s3Service, ?string $name = null)
    {
        $this->entityManager = $entityManager;
        $this->storageConfiguration = $storageConfiguration;
        $this->S3Service = $s3Service;
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('app:readBucketToDb');
        $this->setDescription('first step of migration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->write("\n" . 'Walk along all objects in Archive Bucket, and store basic info about image' . "\n");
        $tiffs = $this->S3Service->listObjects($this->storageConfiguration->getArchiveBucket());
        $output->write("\n" . 'There is '. count($tiffs) . " objects in the bucket. \n");
        foreach ($tiffs as $tiff) {
            $entity = new Photos();
            $entity->setCreatedAt()->setArchiveFilename($tiff)->setJp2Filename($this->storageConfiguration->getJP2ObjectKey($tiff))->setFinalized(false);
            $this->entityManager->persist($entity);
        }
        $this->entityManager->flush();
        return Command::SUCCESS;
    }

}
