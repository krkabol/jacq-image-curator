<?php

declare(strict_types=1);

namespace App\Model\FileManagement;

use Aws\Result;

readonly class File
{
    const int MIN_FILESIZE = 5242880;
    const int MAX_FILESIZE = 398458880;

    const string MIME_TYPE = 'image/tiff';

    public function __construct(public readonly string $name, public readonly Result $info, public readonly bool $alreadyWaiting)
    {

    }

    public function getUploaded(): string
    {
        return $this->info->get("LastModified")->format('j. F Y');
    }

    public function getCreated(): string
    {
        $data = $this->info->get("Metadata");

        if (isset($data["origin-date-iso8601"])) {
            return (new \DateTime($data["origin-date-iso8601"]))->format('j. F Y');

        }
        return "unknown";
    }

    public function getCreatedTimestamp(): ?\DateTimeImmutable
    {
        $data = $this->info->get("Metadata");

        if (isset($data["origin-date-iso8601"])) {
            return new \DateTimeImmutable($data["origin-date-iso8601"]);

        }
        return null;
    }

    public function isSizeOK(): bool
    {
        return $this->getSize() >= self::MIN_FILESIZE && $this->getSize() <= self::MAX_FILESIZE;
    }

    public function getSize(): int
    {
        return (int)$this->info->get("ContentLength");
    }

    public function isTypeOK(): bool
    {
        return $this->info->get("ContentType") === self::MIME_TYPE;
    }

    public function isAlreadyWaiting(): bool
    {
        return $this->alreadyWaiting;
    }

    public function isEligibleToBeImported(): bool
    {
        return ($this->isSizeOK() && $this->isTypeOK() && !$this->isAlreadyWaiting());
    }

}
