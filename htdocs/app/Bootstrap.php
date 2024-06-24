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

        if (getenv('NETTE_ENV', true) === 'development') {
            $configurator->setDebugMode(true);
        }
		//$configurator->setDebugMode('secret@23.75.345.200'); // enable for your remote IP
		$configurator->enableTracy($appDir . '/log');

		$configurator->setTempDirectory($appDir . '/temp');

		$configurator->createRobotLoader()
			->addDirectory(__DIR__)
			->register();

        $configurator->addStaticParameters([
            'rootDir' => realpath(__DIR__ . '/..'),
            'appDir' => __DIR__,
            'wwwDir' => realpath(__DIR__ . '/../www'),
        ]);
        if (getenv('NETTE_ENV', true) === 'development') {
            $configurator->addConfig($appDir . '/config/env/dev.neon');
        } else {
            $configurator->addConfig($appDir . '/config/env/prod.neon');
        }
        $configurator->addConfig($appDir . '/config/local.neon');

        return $configurator;
	}
}
