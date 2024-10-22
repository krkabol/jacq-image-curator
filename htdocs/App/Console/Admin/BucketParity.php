<?php declare(strict_types = 1);

namespace App\Console\Admin;

use App\Model\Database\Entity\Photos;
use App\Model\Database\Entity\PhotosStatus;
use App\Model\Database\EntityManager;
use App\Services\RepositoryConfiguration;
use App\Services\S3Service;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BucketParity extends Command
{

    /**
     * Compare archive and iiif bucket if exactly what expected files are present
     */
    public function __construct(protected readonly EntityManager $entityManager, protected readonly RepositoryConfiguration $repositoryConfiguration, protected readonly S3Service $s3Service, ?string $name = null)
    {
        parent::__construct($name);
    }

    /**
     * @return string[]
     */
    public function getArchiveFilesDb(): array
    {
        $sql = 'SELECT archive_filename FROM photos p WHERE status_id IN (' . implode(',', PhotosStatus::PASSED) . ') ORDER BY archive_filename asc';
        $smtp = $this->entityManager->getConnection()->prepare($sql);

        return $smtp->executeQuery()->fetchFirstColumn();
    }

    /**
     * @return string[]
     */
    public function getJp2FilesDb(): array
    {
        $sql = 'SELECT jp2filename FROM photos p WHERE status_id IN (' . implode(',', PhotosStatus::PASSED) . ') ORDER BY jp2filename asc';
        $smtp = $this->entityManager->getConnection()->prepare($sql);

        return $smtp->executeQuery()->fetchFirstColumn();
    }

    /**
     * @return string[]
     */
    public function getArchiveFilesS3(): array
    {
        return $this->s3Service->listObjectsNamesOnly($this->repositoryConfiguration->getArchiveBucket());
    }

    /**
     * @return string[]
     */
    public function getJp2FilesS3(): array
    {
        return $this->s3Service->listObjectsNamesOnly($this->repositoryConfiguration->getImageServerBucket());
    }

    protected function configure(): void
    {
        $this->setName('admin:bucketParity');
        $this->setDescription('check content of archive and iiif bucket');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startTime = microtime(true);

        $output->writeln("\n Archive from db missing in bucket");
        $diff = array_diff($this->getArchiveFilesDb(), $this->getArchiveFilesS3());
        foreach ($diff as $file) {
            $output->writeln($file);
        }

        $output->writeln("\n Archive from bucket missing in db");
        $diff = array_diff($this->getArchiveFilesS3(), $this->getArchiveFilesDb());
        foreach ($diff as $file) {
            $output->writeln($file);
        }

        $output->writeln("\n Jp2 from db missing in bucket");
        $diff = array_diff($this->getJp2FilesDb(), $this->getJp2FilesS3());
        foreach ($diff as $file) {
            $output->writeln($file);
        }

        $output->writeln("\n Jp2 from bucket missing in db");
        $diff = array_diff($this->getJp2FilesS3(), $this->getJp2FilesDb());
        foreach ($diff as $file) {
            $output->writeln($file);
        }

        $output->writeln(sprintf("\n Execution time: %.2f sec", (microtime(true) - $startTime)));

        return Command::SUCCESS;
    }

}
