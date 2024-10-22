<?php declare(strict_types = 1);

namespace App\Console\Admin;

use App\Model\Database\Entity\Photos;
use App\Model\Database\Entity\PhotosStatus;
use App\Model\Database\EntityManager;
use App\Services\RepositoryConfiguration;
use App\Services\S3Service;
use App\Services\TempDir;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BatchDownload extends Command
{

    /**
     * For development purpose, we may need to download multiple Archive Master files.
     *
     * Do not forget switch S3 service to make the right buckets available, and keep up-to-date local copy of database
     */
    public function __construct(protected readonly EntityManager $entityManager, protected readonly RepositoryConfiguration $repositoryConfiguration, protected readonly S3Service $s3Service, protected readonly TempDir $tempDir, ?string $name = null)
    {
        parent::__construct($name);
    }

    /**
     * @return Photos[]
     */
    public function getPhotos(): array
    {
        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata('App\Model\Database\Entity\Photos', 'p');
        $query = $this->entityManager->createNativeQuery('SELECT p.* FROM photos p WHERE status_id = ? ORDER BY id asc', $rsm);
        $query->setParameter(1, PhotosStatus::HIDDEN);

        return $query->execute();
    }

    protected function configure(): void
    {
        $this->setName('admin:downloadArchive');
        $this->setDescription('downloads subset of Archive Master files');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startTime = microtime(true);
        $photos = $this->getPhotos();
        $output->writeln(count($photos) . ' files will be downloaded.');
        foreach ($photos as $photo) {
            $this->s3Service->getObject($this->repositoryConfiguration->getArchiveBucket(), $photo->getArchiveFilename(), $this->tempDir->getPath('downloaded') . DIRECTORY_SEPARATOR . $photo->getArchiveFilename());
        }

        $output->writeln(sprintf("\n Execution time: %.2f sec", (microtime(true) - $startTime)));

        return Command::SUCCESS;
    }

}
