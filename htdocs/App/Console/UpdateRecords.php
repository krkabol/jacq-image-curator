<?php declare(strict_types=1);

namespace App\Console;

use App\Model\Database\Entity\Photos;
use App\Model\Database\EntityManager;
use App\Model\MigrationStages\BaseStageException;
use App\Services\S3Service;
use App\Services\StorageConfiguration;
use App\Services\TestService;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateRecords extends Command
{

    public function __construct(protected readonly EntityManager $entityManager,protected readonly  StorageConfiguration $storageConfiguration,protected readonly  S3Service $s3Service,protected readonly  TestService $testService, ?string $name = null)
    {
        die("allow carefully only for initial import of data");
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
                /** not ready yet even deprecated https://github.com/doctrine/orm/issues/11313 Criteria:ASC */
        $records = $this->entityManager->getPhotosRepository()->findBy(["finalized" => false, "message" => NULL], ["id"=>Criteria::ASC],15);
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
        $this->entityManager->flush();
        return Command::SUCCESS;
    }

}