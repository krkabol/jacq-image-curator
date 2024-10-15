<?php declare(strict_types = 1);

namespace App\Console\Admin;

use App\Facades\CuratorFacade;
use App\Model\Database\EntityManager;
use App\Services\ImageService;
use App\Services\RepositoryConfiguration;
use App\Services\S3Service;
use App\Services\TempDir;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Test extends Command
{

    public function __construct(protected readonly TempDir $tempDir, protected readonly EntityManager $entityManager, protected readonly RepositoryConfiguration $storageConfiguration, protected readonly S3Service $s3Service, protected readonly CuratorFacade $curatorService, protected readonly ImageService $imageService, ?string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('curator:test');
        $this->setDescription('test');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startTime = microtime(true);
        $imagick = $this->imageService->createImagick($this->tempDir->getPath('test-archive/test_5.tif'));

        $limit = 3000;
        $imagick = $this->imageService->resizeImage($imagick, $limit);

        $imagick->modulateImage(100, 0, 100);
//        $imagick->adaptiveThresholdImage(150, 150, 1);
        $imagick->setImageFormat('png');
        $imagick->setImageCompressionQuality(100);
        $imagick->writeImage($this->tempDir->getPath('output.png'));
        $output->writeln(sprintf("\n Conversion time: %.2f sec", (microtime(true) - $startTime)));

        $outputZbar = [];
        $returnVar = 0;
        $info = exec('zbarimg --quiet --raw ' . escapeshellarg($this->tempDir->getPath('output.png')), $outputZbar, $returnVar);
        var_dump($info);
        var_dump($returnVar);

        var_dump($outputZbar);

        $output->writeln(sprintf("\n Execution time: %.2f sec", (microtime(true) - $startTime)));

        return Command::SUCCESS;
    }

}
