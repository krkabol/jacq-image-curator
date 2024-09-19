<?php

declare(strict_types=1);

namespace App\Core;

use Nette;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;


final class RouterFactory
{
	use Nette\StaticClass;

    public static function createRouter(): RouteList
    {
        $router = new RouteList();

        RouterFactory::buildAdmin($router);
        RouterFactory::buildFront($router);

        return $router;
    }

    protected static function buildAdmin(RouteList $router): RouteList
    {
        $router[] = $list = new RouteList('Admin');
        $list[] = new Route('admin/<presenter>/<action>[/<id>]', 'Home:default');

        return $router;
    }

    protected static function buildFront(RouteList $router): RouteList
    {
        $router[] = $list = new RouteList('Front');
        $list[] = new Route('<presenter>/<action>[/<id>]', 'Home:default');

        return $router;
    }
}
