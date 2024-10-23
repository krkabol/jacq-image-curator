<?php declare(strict_types=1);

namespace Tests\Cases\Unit\Services;

use App\Services\TempDir;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

test('TempDir Service', function (): void {
    $basePath = '/srv/temp';
    $subfolder = 'curator';
    $service = new TempDir($basePath);
    Assert::same($basePath . DIRECTORY_SEPARATOR . $subfolder . DIRECTORY_SEPARATOR, $service->getPath());
    Assert::same($basePath . DIRECTORY_SEPARATOR . $subfolder . DIRECTORY_SEPARATOR, $service->getPath(''));
    Assert::same($basePath . DIRECTORY_SEPARATOR . $subfolder . DIRECTORY_SEPARATOR . "test", $service->getPath('test'));
});

