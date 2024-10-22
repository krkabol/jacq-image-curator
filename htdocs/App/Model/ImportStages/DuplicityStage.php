<?php declare(strict_types=1);

namespace App\Model\ImportStages;

use App\Model\Database\Entity\Photos;
use App\Model\ImportStages\Exceptions\DuplicityStageException;
use App\Services\EntityServices\PhotoService;
use App\Services\ImageService;
use App\Services\RepositoryConfiguration;
use App\Services\S3Service;
use League\Pipeline\StageInterface;
use Nette\Application\LinkGenerator;

readonly class DuplicityStage implements StageInterface
{
    protected Photos $item;

    public function __construct(protected PhotoService $photoService, protected LinkGenerator $linkGenerator, protected ImageService $imageService, protected  RepositoryConfiguration $repositoryConfiguration, protected S3Service $s3Service)
    {
    }

    public function __invoke(mixed $payload): mixed
    {
        $this->item = $payload;
        $duplicities = $this->photoService->findPotentialDuplicates($this->item);
        if (count($duplicities) > 0) {
            $imagickNewFile = $this->imageService->createImagick($this->repositoryConfiguration->getImportTempPath($this->item));
            foreach ($duplicities as $duplicate) {
                $this->s3Service->getObject($this->repositoryConfiguration->getArchiveBucket(), $duplicate->getArchiveFilename(), $this->repositoryConfiguration->getImportTempDuplicatePath($duplicate));

                $imagickFromDuplicateCandidate = $this->imageService->createImagick($this->repositoryConfiguration->getImportTempDuplicatePath($duplicate));
                 if ($imagickNewFile->getImageSignature() === $imagickFromDuplicateCandidate->getImageSignature()) {
                    $this->informAboutDuplicity($duplicate);
                }
                $imagickFromDuplicateCandidate->destroy();
                unset($imagickFromDuplicateCandidate);
            }
            $imagickNewFile->destroy();
            unset($imagickNewFile);
        }

        return $payload;
    }

    protected function informAboutDuplicity(Photos $duplicate): void
    {
        $link = $this->linkGenerator->link(':Front:Repository:specimen', [$duplicate->getFullSpecimenId()], null, 'link');
        throw new DuplicityStageException('suspicious similarity with file ' . $duplicate->getArchiveFilename() . ' already imported to the specimen <a href="' . $link . '" target="duplicity">' . $this->item->getFullSpecimenId() . '</a>');
    }

}
