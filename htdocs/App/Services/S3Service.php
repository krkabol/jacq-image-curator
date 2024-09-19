<?php

declare(strict_types=1);

namespace App\Services;

use Aws\Exception\AwsException;
use Aws\Result;
use Aws\S3\S3Client;
use DateTimeImmutable;
use Nette\Neon\Exception;

class S3Exception extends Exception
{

}

readonly class S3Service
{
    public function __construct(protected S3Client $s3)
    {
    }

    public function bucketsExists(array $buckets): bool
    {
        foreach ($buckets as $bucket) {
            if (!$this->s3->doesBucketExist($bucket)) {
                return false;
            }
        }
        return true;
    }

    public function objectExists(string $bucket, string $object): bool
    {
        return $this->s3->doesObjectExist($bucket, $object);
    }

    public function objectsExists(string $bucket, array $objects): bool
    {
        foreach ($objects as $object) {
            if (!$this->s3->doesObjectExist($bucket, $object)) {
                return false;
            }
        }
        return true;
    }

    public function createBucket(string $name): void
    {
        if (!$this->s3->doesBucketExist($name)) {
            try {
                $result = $this->s3->createBucket(['Bucket' => $name,]);
            } catch (AwsException $e) {
                die("Error during bucket create: " . $e->getMessage() . "\n");
            }
        }
    }

    public function listBuckets(): Result
    {
        return $this->s3->listBuckets();
    }

    public function putTiffIfNotExists(string $bucket, string $key, string $path): Result
    {
        if (!$this->s3->doesObjectExist($bucket, $key)) {
            return $this->s3->putObject([
                'Bucket' => $bucket,
                'Key' => $key,
                'SourceFile' => $path,
                'ContentType' => 'image/tiff']);
        }
        throw new S3Exception("Tif file {$key} already exists");
    }

    public function copyObjectIfNotExists(string $objectKey, string $sourceBucket, string $targetBucket): bool
    {
        throw new \Exception("readonly S3 operations allowed only");
        if (!$this->s3->doesObjectExist($targetBucket, $objectKey)) {
            $this->s3->copyObject([
                'Bucket' => $targetBucket,
                'Key' => $objectKey,
                'CopySource' => "{$sourceBucket}/{$objectKey}",
            ]);
            return true;
        }
        return false;
    }

    public function getObjectSize(string $bucket, string $key): int
    {
        $result = $this->s3->headObject([
            'Bucket' => $bucket,
            'Key' => $key,
        ]);
        return $result['ContentLength'];
    }

    public function headObject($bucket, $key)
    {
        return $this->s3->headObject([
            'Bucket' => $bucket,
            'Key' => $key,
        ]);
    }

    public function getObjectOriginalTimestamp(string $bucket, string $key): ?DateTimeImmutable
    {
        $result = $this->s3->headObject([
            'Bucket' => $bucket,
            'Key' => $key,
        ]);
        $data = $result->get("Metadata");
        if (isset($data["origin-date-iso8601"])) {
            return new \DateTimeImmutable($data["origin-date-iso8601"]);
        }
        return null;
    }

    public function deleteObject(string $bucket, string $key): Result
    {
        return $this->s3->deleteObject([
            'Bucket' => $bucket,
            'Key' => $key,
        ]);
    }

    public function putJP2IfNotExists(string $bucket, string $key, string $path): Result
    {
        if (!$this->s3->doesObjectExist($bucket, $key)) {
            return $this->s3->putObject([
                'Bucket' => $bucket,
                'Key' => $key,
                'SourceFile' => $path,
                'ContentType' => 'image/jp2']);
        }
        throw new S3Exception("JP2 file {$key} already exists");

    }

    public function getObject(string $bucket, string $key, string $path): Result
    {
        return $this->s3->getObject([
            'Bucket' => $bucket,
            'Key' => $key,
            'SaveAs' => $path]);
    }

    public function listObjectsNamesOnly(string $bucket): array
    {
        $objects = [];
        $result = $this->s3->getIterator('ListObjects', array(
            "Bucket" => $bucket,
            // "Prefix" => 'some_folder/'
        ));
        foreach ($result as $object) {
            $objects[] = $object['Key'];
        }
        return $objects;
    }

    public function listObjects(string $bucket): \Iterator
    {
        $objects = [];
        $result = $this->s3->getIterator('ListObjects', array(
            "Bucket" => $bucket,
            // "Prefix" => 'some_folder/'
        ));
        return $result;
    }

    public function getStreamOfObject($bucket, $key)
    {
        $this->s3->registerStreamWrapper();
        return fopen("s3://{$bucket}/{$key}", 'r');
    }
}
