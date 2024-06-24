<?php

declare(strict_types=1);

namespace app\Model\Stages;

use app\Model\Database\Entity\Photos;
use App\Model\Database\EntityManager;
use app\Model\PhotoOfSpecimen;
use app\Services\S3Service;
use app\Services\StorageConfiguration;
use League\Pipeline\StageInterface;

class RegisterStageException extends BaseStageException
{

}

class RegisterStage implements StageInterface
{

    protected EntityManager $entityManager;
    protected StorageConfiguration $configuration;
    protected S3Service $s3Service;


    public function __construct(EntityManager $entityManager, StorageConfiguration $configuration, S3Service $s3Service)
    {
        $this->entityManager = $entityManager;
        $this->configuration = $configuration;
        $this->s3Service = $s3Service;
    }

    public function __invoke($payload)
    {
        try {
            $payload->setJp2Size($this->s3Service->getObjectSize($this->configuration->getJP2Bucket(), $this->configuration->getJP2ObjectKey($payload->getObjectKey())));
            $payload->setTiffSize($this->s3Service->getObjectSize($this->configuration->getArchiveBucket(), $payload->getObjectKey()));
            if ($this->entityManager->getPhotosRepository()->findOneByArchiveFilename($payload->getObjectKey()) !== null) {
                throw new RegisterStageException("already registred file (?): " . $payload->getObjectKey());
            }
            $this->writeRecord($payload);
        } catch (RegisterStageException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            throw new RegisterStageException("db write error (" . $exception->getMessage() . "): " . $payload->getObjectKey());
        }
        return $payload;
    }

    protected function writeRecord($payload)
    {
        /** @var PhotoOfSpecimen $payload */
        $entity = new Photos();
        $herbarium = $this->entityManager->getHerbariaRepository()->findOneByAcronym($payload->getHerbariumAcronym());
        $entity
            ->setCreatedAt()
            ->setArchiveFilename($payload->getObjectKey())
            ->setJp2Filename($this->configuration->getJP2ObjectKey($payload->getObjectKey()))
            ->setFinalized(true)
            ->setHerbarium($herbarium)
            ->setHeight($payload->getHeight())
            ->setWidth($payload->getWidth())
            ->setArchiveFileSize($payload->getTiffSize())
            ->setJP2FileSize($payload->getJp2Size())
            ->setSpecimenId($payload->getSpecimenId());

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }
}
