<?php

declare(strict_types=1);

namespace App\Model\FileManagement;

use Aws\Api\DateTimeResult;
use Aws\Result;

readonly class FileInsideCuratorBucket
{
    const int MIN_FILESIZE = 5242880;
    const int MAX_FILESIZE = 398458880;

    const string EXTENSION = 'tif';
    const string MIME_TYPE = 'image/tiff';

    public function __construct(public readonly string $name, public readonly int $size, public readonly DateTimeResult $timestamp, public readonly bool $alreadyWaiting)
    {
    }

    public function getUploaded(): string
    {
        return $this->timestamp->format('j. F Y');
    }


    public function isSizeOK(): bool
    {
        return $this->size >= self::MIN_FILESIZE && $this->size <= self::MAX_FILESIZE;
    }

    public function isTypeOK(): bool
    {
        return pathinfo($this->name, PATHINFO_EXTENSION) === self::EXTENSION;
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
