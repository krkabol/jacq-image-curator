<?php declare(strict_types = 1);

namespace App;

use Nette\Bootstrap\Configurator;

class Bootstrap
{

    public static function boot(): Configurator
    {
        $configurator = new Configurator();
        $appDir = dirname(__DIR__);

        $configurator->setTempDirectory($appDir . '/temp');
        $configurator->createRobotLoader()->addDirectory(__DIR__)->register();

        $configurator->addDynamicParameters(['env' => getenv()]);

        $environment = getenv('NETTE_ENV', true);
        switch ($environment) {
            case 'development':
                $configurator->addConfig($appDir . '/config/env/dev.neon');
                $configurator->setDebugMode(true);
                break;
            case 'test':
                $configurator->addConfig($appDir . '/config/env/test.neon');
                break;
            default:
                $configurator->addConfig($appDir . '/config/env/prod.neon');
        }

        $configurator->addConfig($appDir . '/config/local.neon');
        $configurator->enableTracy($appDir . '/log');

        return $configurator;
    }

}
