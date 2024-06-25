<?php declare(strict_types=1);

namespace App\Console;

use app\Model\Database\Entity\Photos;
use App\Model\Database\EntityManager;
use App\Model\UpdateStages\BaseStageException;
use app\Services\S3Service;
use app\Services\StorageConfiguration;
use app\Services\TestService;
use Doctrine\Common\Collections\Order;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateRecords extends Command
{
    protected EntityManager $entityManager;
    protected StorageConfiguration $storageConfiguration;
    protected S3Service $S3Service;
    protected TestService $testService;

    public function __construct(EntityManager $entityManager, StorageConfiguration $storageConfiguration, S3Service $s3Service, TestService $testService, ?string $name = null)
    {
        $this->entityManager = $entityManager;
        $this->storageConfiguration = $storageConfiguration;
        $this->S3Service = $s3Service;
        $this->testService = $testService;
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('app:updateRecords');
        $this->setDescription('second step of migration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startTime = microtime(true);
        $output->write("\n" . 'Walk along all records in db and control/update info about image' . "\n");

        $records = $this->entityManager->getPhotosRepository()->findBy(["finalized" => false, "message" => NULL], ["id"=>Order::Ascending],100);
        $output->write("\n" . 'There is ' . count($records) . " not finalized records in the db without \"message\" --> to be processed. \n");
        $pipeline = $this->testService->migrationPipeline();
        $i=0;
        foreach ($records as $record) {
            /** @var Photos $record */
            try {
                $i++;
                $pipeline->process($record);
            } catch (BaseStageException $e) {
                $record->setMessage($e->getMessage())
                    ->setFinalized(false);
            } finally {
                $executionTime = microtime(true) - $startTime;
                $output->writeln(sprintf('Execution time: %.2f sec', $executionTime));
            }
            if ($i >= 10) {
                $this->entityManager->flush();
                $i=0;
            }
        }
        return Command::SUCCESS;
    }

}
