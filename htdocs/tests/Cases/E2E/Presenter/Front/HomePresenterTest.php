<?php declare(strict_types = 1);

namespace Tests\Cases\E2E\Presenter\Front;

use App\Bootstrap;
use App\UI\Front\Home\HomePresenter;
use Nette\Application\Request;
use Nette\Http\RequestFactory;
use Nette\Http\Response;
use Tester\Assert;

require_once __DIR__ . '/../../../../bootstrap.php';

test('Front:Home',function (): void {
	$container = Bootstrap::boot()->createContainer();

	$presenter = $container->getByName($container->findByType(HomePresenter::class)[0]);
	assert($presenter instanceof HomePresenter);

	ob_start();
	$response = $presenter->run(new Request('Front:Home', 'default'));
	$response->send((new RequestFactory())->fromGlobals(), new Response());
	$content = ob_get_clean();
    Assert::contains("JACQ image service", $content);
});
