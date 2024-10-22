<?php declare(strict_types = 1);

namespace App\Console\Admin;

use App\Facades\CuratorFacade;
use App\Model\Database\Entity\Photos;
use App\Model\Database\Entity\PhotosStatus;
use App\Model\Database\EntityManager;
use App\Services\ImageService;
use App\Services\RepositoryConfiguration;
use App\Services\S3Service;
use App\Services\TempDir;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RefreshJp2 extends Command
{

    protected const string TEMPNAME = DIRECTORY_SEPARATOR . 'exif.tif';
    protected const string TEMPNAME2 = DIRECTORY_SEPARATOR . 'exif.jp2';

    public function __construct(protected readonly EntityManager $entityManager, protected readonly CuratorFacade $curatorService, protected readonly TempDir $tempDir, protected readonly ImageService $imageService, protected RepositoryConfiguration $repositoryConfiguration, protected S3Service $s3Service, ?string $name = null)
    {
        parent::__construct($name);
    }

    /**
     * @return Photos[]
     */
    public function getListOfPhotos(): ?array
    {
        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata('App\Model\Database\Entity\Photos', 'p');
        $query = $this->entityManager->createNativeQuery('SELECT p.* FROM photos p WHERE status_id IN (?) AND id > 30274 ORDER BY id asc', $rsm);
        $query->setParameter(1, PhotosStatus::PASSED);

        return $query->execute();
    }

    protected function configure(): void
    {
        $this->setName('admin:refreshJp2');
        $this->setDescription('generate new JP2 files for image server');
    }

    protected function tempFile(): string
    {
        return $this->tempDir->getPath(self::TEMPNAME);
    }

    protected function tempFile2(): string
    {
        return $this->tempDir->getPath(self::TEMPNAME2);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startTime = microtime(true);
        foreach ($this->getListOfPhotos() as $photo) {
            $output->write("\n photoId: " . $photo->getId() . "\n");
            $this->curatorService->getArchiveFile($photo, $this->tempFile());

            $imagick = $this->imageService->createImagick($this->tempFile());
            $imagick->setImageFormat('jp2');
            $imagick->setImageCompressionQuality($this->repositoryConfiguration->getJp2Quality());
            $imagick->writeImage($this->tempFile2());
            $imagick->destroy();
            unset($imagick);
            $photo->setJp2FileSize(filesize($this->tempFile2()));
            $photo->setLastEditAt();
            $this->entityManager->flush();
            unlink($this->tempFile());

            $this->s3Service->deleteObject($this->repositoryConfiguration->getImageServerBucket(), $this->repositoryConfiguration->createS3Jp2Name($photo));
            $this->s3Service->putJp2IfNotExists($this->repositoryConfiguration->getImageServerBucket(), $this->repositoryConfiguration->createS3Jp2Name($photo), $this->tempFile2());
            unlink($this->tempFile2());
        }

        $output->writeln(sprintf("\n Execution time: %.2f sec", (microtime(true) - $startTime)));

        return Command::SUCCESS;
    }

}
