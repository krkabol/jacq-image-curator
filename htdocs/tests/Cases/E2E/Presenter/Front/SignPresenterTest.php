<?php declare(strict_types = 1);

namespace Tests\Cases\E2E\Presenter\Front;

use App\Bootstrap;
use App\UI\Front\Sign\SignPresenter;
use Nette\Application\Request;
use Nette\Http\RequestFactory;
use Nette\Http\Response;
use Tester\Assert;

require_once __DIR__ . '/../../../../bootstrap.php';

test('Front:Sign:in',function (): void {
	$container = Bootstrap::boot()->createContainer();

	$presenter = $container->getByName($container->findByType(SignPresenter::class)[0]);
	assert($presenter instanceof SignPresenter);

	ob_start();
	$response = $presenter->run(new Request('Front:Sign', 'default', ["action" => "in"]));
	$response->send((new RequestFactory())->fromGlobals(), new Response());
	$content = ob_get_clean();
    Assert::contains("Sign in", $content);
    Assert::contains("NRP KA 3.3", $content);

});
