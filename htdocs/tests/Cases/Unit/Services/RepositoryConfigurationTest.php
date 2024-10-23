<?php declare(strict_types=1);

namespace Tests\Cases\Unit\Services;

use App\Bootstrap;
use App\Services\RepositoryConfiguration;
use App\Services\TempDir;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

test('RepositoryConfiguration Service', function (): void {
    $container = Bootstrap::boot()->createContainer();
    $tempDir = $container->getByType(TempDir::class);
    $parameters = $container->getParameters();
    $service = new RepositoryConfiguration($parameters["storage"], $tempDir);

    Assert::type('string', $service->getArchiveBucket());
    Assert::notEqual('', $service->getArchiveBucket());

    Assert::type('string', $service->getImageServerBucket());
    Assert::notEqual('', $service->getImageServerBucket());

    Assert::type('int', $service->getJp2Quality());
    Assert::true($service->getJp2Quality() >= 0 && $service->getJp2Quality() <= 100 );

    Assert::type('string', $service->getBarcodeRegex());
    Assert::notEqual('', $service->getBarcodeRegex());

    Assert::type('string', $service->getRegexSpecimenPartName());
    Assert::notEqual('', $service->getRegexSpecimenPartName());

    Assert::type('string', $service->getRegexHerbariumPartName());
    Assert::notEqual('', $service->getRegexHerbariumPartName());

    Assert::type('string', $service->getSpecimenNameRegex());
    Assert::notEqual('', $service->getSpecimenNameRegex());

    Assert::type('int', $service->getZbarImageSize());
    Assert::true($service->getZbarImageSize() >= 500 && $service->getZbarImageSize() <= 10000 );

    Assert::type('int', $service->getThumbnailSize());
    Assert::true($service->getThumbnailSize() >= 10 && $service->getThumbnailSize() <= 10000 );

    Assert::type('int', $service->getPreviewSize());
    Assert::true($service->getPreviewSize() >= 100 && $service->getPreviewSize() <= 10000 );

    Assert::type('int', $service->getPreviewQuality());
    Assert::true($service->getPreviewQuality() >= 0 && $service->getPreviewQuality() <= 100 );

    Assert::type('string', $service->getImportTempJp2Path());
    Assert::notEqual('', $service->getImportTempJp2Path());

    Assert::type('string', $service->getImportTempZbarPath());
    Assert::notEqual('', $service->getImportTempZbarPath());

});

