<?php declare(strict_types = 1);

namespace App\Model\FileManagement;

use Aws\Api\DateTimeResult;

readonly class FileInsideCuratorBucket
{

    public const int MIN_FILESIZE = 5242880;
    public const int MAX_FILESIZE = 398458880;

    public const string EXTENSION = 'tif';
    public const string MIME_TYPE = 'image/tiff';

    public function __construct(public readonly string $name, public readonly int $size, public readonly DateTimeResult $timestamp, public readonly bool $alreadyWaiting, public readonly bool $hasControlError, public readonly ?int $rowId, public readonly ?string $controlMsg)
    {
    }

    public function getUploaded(): string
    {
        return $this->timestamp->format('j. F Y');
    }

    public function isSizeOk(): bool
    {
        return $this->size >= self::MIN_FILESIZE && $this->size <= self::MAX_FILESIZE;
    }

    public function isTypeOk(): bool
    {
        return pathinfo($this->name, PATHINFO_EXTENSION) === self::EXTENSION;
    }

    public function isAlreadyWaiting(): bool
    {
        return $this->alreadyWaiting;
    }

    public function hasControlError(): bool
    {
        return $this->hasControlError;
    }

    public function getControlMsg(): ?string
    {
        return $this->controlMsg;
    }

    public function isEligibleToBeImported(): bool
    {
        return $this->isSizeOk() && $this->isTypeOk() && !$this->isAlreadyWaiting() && !$this->hasControlError();
    }

}
