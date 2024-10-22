<?php declare(strict_types = 1);

namespace Tests\Cases\E2E\Presenter\Front;

use App\Bootstrap;
use App\UI\Front\Repository\RepositoryPresenter;
use Nette\Application\Request;
use Nette\Http\RequestFactory;
use Nette\Http\Response;
use Tester\Assert;

require_once __DIR__ . '/../../../../bootstrap.php';

test('Front:Repository:specimen:empty',function (): void {
	$container = Bootstrap::boot()->createContainer();

    ob_start();
	$presenter = $container->getByName($container->findByType(RepositoryPresenter::class)[0]);
	assert($presenter instanceof RepositoryPresenter);
    $response = $presenter->run(new Request('Front:Repository', 'default', ["action" => "specimen"]));
    $response->send((new RequestFactory())->fromGlobals(), new Response());
    $content = ob_get_clean();
    Assert::contains("Redirect", $content);

});

