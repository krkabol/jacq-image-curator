<?php declare(strict_types=1);

namespace App\Console;

use App\Model\Database\EntityManager;
use App\Services\CuratorService;
use app\Services\S3Service;
use app\Services\StorageConfiguration;
use app\Services\TempDir;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Test extends Command
{

    public function __construct(protected readonly TempDir $tempDir, protected readonly EntityManager $entityManager, protected readonly StorageConfiguration $storageConfiguration, protected readonly S3Service $s3Service, protected readonly CuratorService $curatorService, ?string $name = null)
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
        $imagick = new \Imagick($this->tempDir->getPath("barcode/test-archive/test_5b.tif"));

        $page = $this->getLargestImage($imagick);
        $imagick->setIteratorIndex($page);

        $width = $imagick->getImageWidth();
        $height = $imagick->getImageHeight();
        $limit = 3000;
        if ($width > $limit || $height > $limit) {
            if ($width > $height) {
                $newWidth = $limit;
                $newHeight = intval(($limit / $width) * $height);
            } else {
                $newHeight = $limit;
                $newWidth = intval(($limit / $height) * $width);
            }
            $imagick->resizeImage($newWidth, $newHeight, \Imagick::FILTER_GAUSSIAN, 1);
        }

        $imagick->modulateImage(100, 0, 100);
        $imagick->adaptiveThresholdImage(150, 150, 1);
        $imagick->setImageFormat('jpg');
        $imagick->setImageCompressionQuality(80);
        $imagick->writeImage($this->tempDir->getPath("barcode/test-archive/output.jpg"));
        $output->writeln(sprintf("\n Conversion time: %.2f sec", (microtime(true) - $startTime)));


        $outputZbar = [];
        $returnVar = 0;
        $info = exec("zbarimg --quiet --raw " . escapeshellarg($this->tempDir->getPath("barcode/test-archive/output.jpg")), $outputZbar, $returnVar);
        var_dump($returnVar);

        var_dump($outputZbar);


        $output->writeln(sprintf("\n Execution time: %.2f sec", (microtime(true) - $startTime)));
        return Command::SUCCESS;
    }

    public function getLargestImage(\Imagick $image): int
    {
        $numberOfImages = $image->getNumberImages();

        $maxWidth = 0;
        $maxHeight = 0;
        $largestImage = null;

        for ($i = 0; $i < $numberOfImages; $i++) {
            $image->setIteratorIndex($i);
            $width = $image->getImageWidth();
            $height = $image->getImageHeight();

            if ($width * $height > $maxWidth * $maxHeight) {
                $maxWidth = $width;
                $maxHeight = $height;
                $largestImage = $i;
            }

        }
        return $largestImage;

    }

}
