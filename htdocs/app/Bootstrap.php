<?php

declare(strict_types=1);

namespace app;

use Nette\Bootstrap\Configurator;


class Bootstrap
{
    public static function boot(): Configurator
    {
        $configurator = new Configurator;
        $appDir = dirname(__DIR__);

        //$configurator->setDebugMode('secret@23.75.345.200'); // enable for your remote IP
        $configurator->enableTracy($appDir . '/log');
        $configurator->setTempDirectory($appDir . '/temp');

        $configurator->createRobotLoader()
            ->addDirectory(__DIR__)
            ->register();

        $configurator->addDynamicParameters([
            'env' => getenv(),
        ]);

        $environment = getenv('NETTE_ENV', true);
        switch ($environment) {
            case "development":
                $configurator->setDebugMode(true);
                $configurator->addConfig($appDir . '/config/env/dev.neon');
                break;
            case "test":
                $configurator->addConfig($appDir . '/config/env/test.neon');
                break;
            default:
                $configurator->addConfig($appDir . '/config/env/prod.neon');
        }
        $configurator->addConfig($appDir . '/config/local.neon');
        return $configurator;
    }
}
