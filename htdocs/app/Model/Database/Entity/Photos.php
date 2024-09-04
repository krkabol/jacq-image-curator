<?php

namespace app\Model\Database\Entity;

use app\Model\Database\Entity\Attributes\TCreatedAt;
use app\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TLastEditAt;
use App\Model\Database\Entity\Attributes\TOriginalFileAt;
use app\Model\Database\Repository\PhotosRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PhotosRepository::class)]
#[ORM\Table(name: 'photos', options: ["comment" => "Specimen photos"])]
class Photos
{

    use TId;
    use TCreatedAt;
    use TLastEditAt;
    use TOriginalFileAt;

    #[ORM\Column(unique: true, nullable: true, options: ["comment" => "Filename of Archive Master TIF file"])]
    protected string $archiveFilename;

    #[ORM\Column(nullable: true, options: ["comment" => "Filename that was provided during curator upload, could make sense or completely missing semantical content"])]
    protected string $originalFilename;

    #[ORM\Column(unique: true, nullable: true, options: ["comment" => "Filename of JP2 file"])]
    protected string $jp2Filename;

    #[ORM\ManyToOne(targetEntity: "Herbaria", inversedBy: "photos")]
    #[ORM\JoinColumn(name: "herbarium_id", referencedColumnName: "id", options: ["comment" => "Herbarium storing and managing the specimen data"])]
    protected Herbaria $herbarium;

    #[ORM\ManyToOne(targetEntity: "PhotosStatus")]
    #[ORM\JoinColumn(name: "status_id", referencedColumnName: "id", nullable: false, options: ["comment" => "Status of the photo"])]
    protected PhotosStatus $status;

    #[ORM\Column(type: Types::STRING, nullable: true, options: ["comment" => "Herbarium internal unique id of specimen in form without herbarium acronym"])]
    protected ?string $specimenId;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ["comment" => "Width of image with pixels"])]
    protected ?int $width;
    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ["comment" => "Height of image in pixels"])]
    protected ?int $height;

    #[ORM\Column(type: Types::BIGINT, nullable: true, options: ["comment" => "Filesize of Archive Master TIFF file in bytes"])]
    protected ?int $archiveFileSize;

    #[ORM\Column(type: Types::BIGINT, nullable: true, options: ["comment" => "Filesize of converted JP2 file in bytes"])]
    protected ?int $JP2FileSize;

    #[ORM\Column(type: Types::TEXT, length: 60000,nullable: true, options: ["comment" => "Result of migration"])]
    protected ?string $message;



    public function getArchiveFilename(): string
    {
        return $this->archiveFilename;
    }

    public function setArchiveFilename(string $archiveFilename): Photos
    {
        $this->archiveFilename = $archiveFilename;
        return $this;
    }

    public function getJp2Filename(): string
    {
        return $this->jp2Filename;
    }

    public function setJp2Filename(string $jp2Filename): Photos
    {
        $this->jp2Filename = $jp2Filename;
        return $this;
    }



    public function getHerbarium(): Herbaria
    {
        return $this->herbarium;
    }

    public function setHerbarium(Herbaria $herbarium): Photos
    {
        $this->herbarium = $herbarium;
        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(?int $width): Photos
    {
        $this->width = $width;
        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(?int $height): Photos
    {
        $this->height = $height;
        return $this;
    }

    public function getSpecimenId(): ?string
    {
        return $this->specimenId;
    }

    public function setSpecimenId(?string $specimenId): Photos
    {
        $this->specimenId = ltrim($specimenId, '0');
        return $this;
    }

    public function getArchiveFileSize(): ?int
    {
        return $this->archiveFileSize;
    }

    public function setArchiveFileSize(?int $archiveFileSize): Photos
    {
        $this->archiveFileSize = $archiveFileSize;
        return $this;
    }

    public function getJP2FileSize(): ?int
    {
        return $this->JP2FileSize;
    }

    public function setJP2FileSize(?int $JP2FileSize): Photos
    {
        $this->JP2FileSize = $JP2FileSize;
        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): Photos
    {
        $this->message = $message;
        return $this;
    }

    public function getFullSpecimenId(): string
    {
        return $this->getHerbarium()->getAcronym()."_".sprintf('%06d', $this->getSpecimenId());
    }

    public function getStatus(): PhotosStatus
    {
        return $this->status;
    }

    public function setStatus(PhotosStatus $status): Photos
    {
        $this->status = $status;
        return $this;
    }

    public function getOriginalFilename(): ?string
    {
        return $this->originalFilename;
    }

    public function setOriginalFilename(string $originalFilename): Photos
    {
        $this->originalFilename = $originalFilename;
        return $this;
    }



}
