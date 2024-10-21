<?php declare(strict_types=1);

namespace App\Console\Admin;

use App\Facades\CuratorFacade;
use App\Model\Database\Entity\PhotosStatus;
use App\Model\Database\EntityManager;
use App\Services\ImageService;
use App\Services\TempDir;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HarvestExif extends Command
{

    protected const string TEMPNAME = DIRECTORY_SEPARATOR."exif.tif";
    public function __construct(protected readonly EntityManager $entityManager,  protected readonly CuratorFacade $curatorService, protected readonly TempDir $tempDir, protected readonly ImageService $imageService, ?string $name = null)
    {
        parent::__construct($name);
    }

    public function getListOfPhotos(): ?array
    {
        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata('App\Model\Database\Entity\Photos', 'p');
        $query = $this->entityManager->createNativeQuery('SELECT p.* FROM photos p WHERE status_id IN (?) AND identify is null ORDER BY id asc', $rsm);
        $query->setParameter(1, PhotosStatus::PASSED);
        return $query->execute();
    }

    protected function configure(): void
    {
        $this->setName('admin:harvestExif');
        $this->setDescription('harvest Exif and Identify metadata from images uploaded before the repository was established');
    }

    protected function tempFile():string
    {
        return $this->tempDir->getPath(self::TEMPNAME);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startTime = microtime(true);
        foreach ($this->getListOfPhotos() as $photo) {
            $output->write("\n photoId: " . $photo->getId() . "\n");
            $this->curatorService->getArchiveFile($photo, $this->tempFile());

            $imagick = $this->imageService->createImagick($this->tempFile());
            $photo->setIdentify($this->imageService->readIdentify($imagick));
            $photo->setExif($this->imageService->readExif($imagick));
            $imagick->destroy();
            unset($imagick);

            $photo->setLastEditAt();
            $this->entityManager->flush();
            unlink($this->tempFile());
        }
        $output->writeln(sprintf("\n Execution time: %.2f sec", (microtime(true) - $startTime)));
        return Command::SUCCESS;
    }

}
