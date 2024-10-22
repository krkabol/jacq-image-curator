<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TLastEditAt;
use App\Model\Database\Entity\Attributes\TOriginalFileAt;
use App\Model\Database\Repository\PhotosRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Entity(repositoryClass: PhotosRepository::class)]
#[Table(name: 'photos', options: ['comment' => 'Specimen photos'])]
class Photos
{

    use TId;
    use TCreatedAt;
    use TLastEditAt;
    use TOriginalFileAt;

    #[Column(unique: true, nullable: true, options: ['comment' => 'Filename of Archive Master TIF file'])]
    protected ?string $archiveFilename;

    #[Column(nullable: true, options: ['comment' => 'Filename that was provided during curator upload, could make sense or completely missing semantical content'])]
    protected string $originalFilename;

    #[Column(unique: true, nullable: true, options: ['comment' => 'Filename of JP2 file'])]
    protected ?string $jp2Filename;

    #[ManyToOne(targetEntity: Herbaria::class, inversedBy: 'photos')]
    #[JoinColumn(name: 'herbarium_id', referencedColumnName: 'id', nullable: false, options: ['comment' => 'Herbarium storing and managing the specimen data'])]
    protected Herbaria $herbarium;

    #[ManyToOne(targetEntity: PhotosStatus::class)]
    #[JoinColumn(name: 'status_id', referencedColumnName: 'id', nullable: false, options: ['comment' => 'Status of the photo'])]
    protected PhotosStatus $status;

    #[Column(type: Types::STRING, nullable: true, options: ['comment' => 'Herbarium internal unique id of specimen in form without herbarium acronym'])]
    protected ?string $specimenId;

    #[Column(type: Types::INTEGER, nullable: true, options: ['comment' => 'Width of image with pixels'])]
    protected ?int $width;

    #[Column(type: Types::INTEGER, nullable: true, options: ['comment' => 'Height of image in pixels'])]
    protected ?int $height;

    #[Column(type: Types::BIGINT, nullable: true, options: ['comment' => 'Filesize of Archive Master TIFF file in bytes'])]
    protected ?int $archiveFileSize;

    #[Column(type: Types::BIGINT, nullable: true, options: ['comment' => 'Filesize of converted JP2 file in bytes'])]
    protected ?int $JP2FileSize;

    #[Column(type: Types::TEXT, length: 60000, nullable: true, options: ['comment' => 'Result of migration'])]
    protected ?string $message;

    #[Column(type: Types::BLOB, nullable: true, options: ['comment' => 'Thumbnail during import phase'])]
    protected mixed $thumbnail;

    /** @var ?mixed[] */
    #[Column(type: Types::JSON, nullable: true, options: ['jsonb' => true, 'comment' => 'raw EXIF data extracted from Archive Master file'])]
    protected ?array $exif;

    /** @var ?mixed[] */
    #[Column(type: Types::JSON, nullable: true, options: ['jsonb' => true, 'comment' => 'Imagick -verbose identify metadata output from the Archive Master file'])]
    protected ?array $identify;

    public function getArchiveFilename(): ?string
    {
        return $this->archiveFilename;
    }

    public function setArchiveFilename(string $archiveFilename): Photos
    {
        $this->archiveFilename = $archiveFilename;

        return $this;
    }

    public function getJp2Filename(): ?string
    {
        return $this->jp2Filename;
    }

    public function setJp2Filename(string $jp2Filename): Photos
    {
        $this->jp2Filename = $jp2Filename;

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

    public function getArchiveFileSize(): ?int
    {
        return $this->archiveFileSize;
    }

    public function setArchiveFileSize(?int $archiveFileSize): Photos
    {
        $this->archiveFileSize = $archiveFileSize;

        return $this;
    }

    public function getJp2FileSize(): ?int
    {
        return $this->JP2FileSize;
    }

    public function setJp2FileSize(?int $JP2FileSize): Photos
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
        return strtoupper($this->getHerbarium()->getAcronym()) . '_' . sprintf('%06d', $this->getSpecimenId());
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

    public function getSpecimenId(): ?string
    {
        return $this->specimenId;
    }

    public function setSpecimenId(?string $specimenId): Photos
    {
        $this->specimenId = $specimenId === null || $specimenId === '' ? null : ltrim($specimenId, '0');

        return $this;
    }

    public function getJacqPid(): string
    {
        return 'https://' . strtolower($this->getHerbarium()->getAcronym()) . '.jacq.org/' . strtoupper($this->getHerbarium()->getAcronym()) . $this->getSpecimenId();
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

    public function getThumbnail(): mixed
    {
        return $this->thumbnail;
    }

    public function setThumbnail(mixed $thumbnail): Photos
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    /**
     * @return ?mixed[]
     */
    public function getExif(): ?array
    {
        return $this->exif;
    }

    /**
     * @param ?mixed[] $exif
     */
    public function setExif(?array $exif): Photos
    {
        $this->exif = $exif;

        return $this;
    }

    /**
     * @return ?mixed[]
     */
    public function getIdentify(): ?array
    {
        return $this->identify;
    }

    /**
     * @param ?mixed[] $identify
     */
    public function setIdentify(?array $identify): Photos
    {
        $this->identify = $identify;

        return $this;
    }

}
