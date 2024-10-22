<?php declare(strict_types=1);

namespace App\Console\Scheduled;

use App\Facades\CuratorFacade;
use App\Model\Database\Entity\Photos;
use App\Model\Database\Entity\PhotosStatus;
use App\Model\Database\EntityManager;
use App\Model\ImportStages\Exceptions\ImportStageException;
use App\Services\RepositoryConfiguration;
use App\Services\S3Service;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProceedCuratorImage extends Command
{

    public const int LIMIT = 10;

    /**
     * Running as a CronJob - process images from curatorBucket to the repository waiting room
     */
    public function __construct(protected readonly EntityManager $entityManager, protected readonly RepositoryConfiguration $storageConfiguration, protected readonly S3Service $s3Service, protected readonly CuratorFacade $curatorService, ?string $name = null)
    {
        parent::__construct($name);
    }

    public function getPhoto(): ?Photos
    {
        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata('App\Model\Database\Entity\Photos', 'p');
        $query = $this->entityManager->createNativeQuery('SELECT p.* FROM photos p WHERE status_id = ? ORDER BY id asc FOR UPDATE SKIP LOCKED LIMIT 1 ', $rsm);
        $query->setParameter(1, PhotosStatus::WAITING);
        try {
            /** @var Photos $photo */
            $photo = $query->getSingleResult();
        } catch (NoResultException $e) {
            return null;
        }

        return $photo;
    }

    protected function configure(): void
    {
        $this->setName('curator:importImage');
        $this->setDescription(sprintf('take %c image(s) from curator bucket and proceed import', self::LIMIT));
    }

    protected function proceedPhoto(OutputInterface $output): int
    {

        $this->entityManager->getConnection()->beginTransaction(); //we are locking the selected row
        $photo = $this->getPhoto();
        if ($photo === null) {
            $this->entityManager->getConnection()->rollBack();

            return Command::SUCCESS;
        }
        try {
            $output->write("\n filename: s3://" . $photo->getHerbarium()->getBucket() . '/' . $photo->getOriginalFilename() . "\n");
            $photo->setLastEditAt();
            $photo->setMessage(null);
            $this->curatorService->importNewFiles()->process($photo);
            $photo->setThumbnail(null);
            $photo->setStatus($this->entityManager->getReference(PhotosStatus::class, PhotosStatus::CONTROL_OK));
        } catch (ImportStageException $e) {
            $photo->setMessage($e->getMessage())
                ->setStatus($this->entityManager->getReference(PhotosStatus::class, PhotosStatus::CONTROL_ERROR));
            $output->write("\n ERROR: " . $e->getMessage() . "\n");
        } catch (\Throwable $e) {
            $this->entityManager->getConnection()->rollBack();
            $output->write("\n ERROR: " . $e->getMessage() . "\n");

            return Command::FAILURE;
        }
        $this->entityManager->flush();
        $this->entityManager->getConnection()->commit();

        return Command::SUCCESS;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startTime = microtime(true);
        for ($i = 0; $i < self::LIMIT; $i++) {
            $individualTaskResult = $this->proceedPhoto($output);
            if ($individualTaskResult === Command::FAILURE) {
                return Command::FAILURE;
            }
        }
        $output->writeln(sprintf("\n Execution time: %.2f sec", (microtime(true) - $startTime)));
        return Command::SUCCESS;
    }

}
