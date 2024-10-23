<?php declare(strict_types=1);

namespace Tests\Cases\Unit\Services;

use App\Services\WebDir;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

test('WebDir Service', function (): void {
    $basePath = '/srv/www';
    $service = new WebDir($basePath);
    Assert::same($basePath . DIRECTORY_SEPARATOR, $service->getPath());
    Assert::same($basePath . DIRECTORY_SEPARATOR, $service->getPath(''));
    Assert::same($basePath . DIRECTORY_SEPARATOR . "test", $service->getPath('test'));
});

