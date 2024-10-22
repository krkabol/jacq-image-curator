<?php declare(strict_types = 1);

namespace App\Services;

use App\Exceptions\S3Exception;
use Aws\Result;
use Aws\S3\S3Client;
use DateTimeImmutable;

readonly class S3Service
{

    public function __construct(protected S3Client $s3)
    {
    }

    public function objectExists(string $bucket, string $object): bool
    {
        return $this->s3->doesObjectExist($bucket, $object);
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

        throw new S3Exception(sprintf('Tif file %s already exists', $key));
    }

    public function getObjectSize(string $bucket, string $key): int
    {
        $result = $this->s3->headObject([
            'Bucket' => $bucket,
            'Key' => $key,
        ]);

        return $result['ContentLength'];
    }

    public function headObject(string $bucket, string $key): Result
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
        $data = $result->get('Metadata');
        if (isset($data['origin-date-iso8601'])) {
            return new \DateTimeImmutable($data['origin-date-iso8601']);
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

    public function putJp2IfNotExists(string $bucket, string $key, string $path): Result
    {
        if (!$this->s3->doesObjectExist($bucket, $key)) {
            return $this->s3->putObject([
                'Bucket' => $bucket,
                'Key' => $key,
                'SourceFile' => $path,
                'ContentType' => 'image/jp2']);
        }

        throw new S3Exception(sprintf('JP2 file %s already exists', $key));
    }

    public function getObject(string $bucket, string $key, string $path): Result
    {
        return $this->s3->getObject([
            'Bucket' => $bucket,
            'Key' => $key,
            'SaveAs' => $path]);
    }

    /**
     * @return string[]
     */
    public function listObjectsNamesOnly(string $bucket): array
    {
        $objects = [];
        $result = $this->s3->getIterator('ListObjects', [
            'Bucket' => $bucket,
            // "Prefix" => 'some_folder/'
        ]);
        foreach ($result as $object) {
            $objects[] = $object['Key'];
        }

        return $objects;
    }

    public function listObjects(string $bucket): \Iterator
    {
        return $this->s3->getIterator('ListObjects', [
            'Bucket' => $bucket,
            // "Prefix" => 'some_folder/'
        ]);
    }

    public function getStreamOfObject(string $bucket, string $key): mixed
    {
        $this->s3->registerStreamWrapper();

        return fopen(sprintf('s3://%s/%s', $bucket, $key), 'r');
    }

}
