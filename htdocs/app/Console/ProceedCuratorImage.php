<?php declare(strict_types=1);

namespace App\Console;

use app\Model\Database\Entity\Photos;
use app\Model\Database\Entity\PhotosStatus;
use App\Model\Database\EntityManager;
use app\Model\ImportStages\ImportStageException;
use App\Services\CuratorService;
use app\Services\S3Service;
use app\Services\StorageConfiguration;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProceedCuratorImage extends Command
{
    /**
     * Running as a CronJob - process images from curatorBucket to the repository waiting room     *
     *
     * for testing on local machine:
     * docker run --network host -v ./htdocs:/app -w /app/bin ghcr.io/krkabol/curator_base:main ./cron_curator_importImage.sh
     */


    public function __construct(protected readonly EntityManager $entityManager, protected readonly StorageConfiguration $storageConfiguration, protected readonly S3Service $s3Service, protected readonly CuratorService $curatorService, ?string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('curator:importImage');
        $this->setDescription('take an image from curator bucket and prepare necessary files');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startTime = microtime(true);
        $this->entityManager->getConnection()->beginTransaction(); //we are locking the selected row
        $photo = $this->getPhoto();
        if ($photo === NULL) {
            $this->entityManager->getConnection()->rollBack();
            return Command::SUCCESS;
        }

        try {
            $output->write("\n filename:" . $photo->getHerbarium()->getAcronym() . $photo->getOriginalFilename() . "\n");
            $this->curatorService->getImportPipeline()->process($photo);
//            $photo->setStatus($this->entityManager->getReference(PhotosStatus::class, PhotosStatus::CONTROL_OK));
        } catch (ImportStageException $e) {
            $photo->setMessage($e->getMessage())
                ->setStatus($this->entityManager->getReference(PhotosStatus::class, PhotosStatus::CONTROL_ERROR));
            $output->write("\n ERROR: " . $e->getMessage() . "\n");
        } catch (\Exception $e) {
            $this->entityManager->getConnection()->rollBack();
            $output->write("\n ERROR: " . $e->getMessage() . "\n");
            return Command::FAILURE;
        } finally {
            $output->writeln(sprintf('Execution time: %.2f sec', (microtime(true) - $startTime)));
        }
        $this->entityManager->flush();
        $this->entityManager->getConnection()->commit();
        return Command::SUCCESS;
    }

    public function getPhoto(): ?Photos
    {
        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata('app\Model\Database\Entity\Photos', 'p');
        $query = $this->entityManager->createNativeQuery('SELECT p.* FROM photos p WHERE status_id = ? ORDER BY id asc FOR UPDATE SKIP LOCKED LIMIT 1 ', $rsm);
        $query->setParameter(1, PhotosStatus::WAITING);
        /** @var Photos $photo */
        $photo = $query->getSingleResult();
        return $photo;
    }

}
