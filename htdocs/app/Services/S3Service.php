<?php

declare(strict_types=1);

namespace app\Services;

use Aws\Exception\AwsException;
use Aws\Result;
use Aws\S3\S3Client;
use Nette\Neon\Exception;

class S3Service
{
    private S3Client $s3;

    public function __construct(S3Client $s3)
    {
        $this->s3 = $s3;
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
        if (!$this->s3->doesObjectExist($bucket, $object)) {
            return false;
        }
        return true;
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

    public function putTiffIfNotExists(string $bucket, string $key, string $path): bool
    {
        if (!$this->s3->doesObjectExist($bucket, $key)) {
            $result = $this->s3->putObject([
                'Bucket' => $bucket,
                'Key' => $key,
                'SourceFile' => $path,
                'ContentType' => 'image/tiff']);
            return true;
        }
        return false;
    }

    public function copyObjectIfNotExists(string $objectKey, string $sourceBucket, string $targetBucket): bool
    {
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

    public function deleteObject(string $bucket, string $key)
    {
        $this->s3->deleteObject([
            'Bucket' => $bucket,
            'Key' => $key,
        ]);
    }

    public function putJP2Overwrite(string $bucket, string $key, string $path): void
    {
        $result = $this->s3->putObject([
            'Bucket' => $bucket,
            'Key' => $key,
            'SourceFile' => $path,
            'ContentType' => 'image/jp2']);

    }

    public function getObject(string $bucket, string $key, string $path): Result
    {
        return $this->s3->getObject([
            'Bucket' => $bucket,
            'Key' => $key,
            'SaveAs' => $path]);
    }

    public function listObjects(string $bucket): array
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

    public function getStreamOfObject($bucket, $key)
    {
        $this->s3->registerStreamWrapper();
        return fopen("s3://{$bucket}/{$key}", 'r');
    }
}
