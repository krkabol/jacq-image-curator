<?php declare(strict_types = 1);

namespace Tests\Cases\E2E\Presenter\Front;

use App\Bootstrap;
use App\UI\Front\Iiif\IiifPresenter;
use Nette\Application\BadRequestException;
use Nette\Application\Request;
use Nette\Http\RequestFactory;
use Nette\Http\Response;
use Tester\Assert;

require_once __DIR__ . '/../../../../bootstrap.php';

test('Front:Iiif:manifest:empty',function (): void {
	$container = Bootstrap::boot()->createContainer();

	$presenter = $container->getByName($container->findByType(IiifPresenter::class)[0]);
	assert($presenter instanceof IiifPresenter);
    Assert::exception(fn()=> $presenter->run(new Request('Front:Iiif', 'default', ["action" => "manifest"])),BadRequestException::class, 'Missing parameter $id required by App\UI\Front\Iiif\IiifPresenter::actionManifest()');

});


test('Front:Iiif:manifest:wrong',function (): void {
    $container = Bootstrap::boot()->createContainer();

    $presenter = $container->getByName($container->findByType(IiifPresenter::class)[0]);
    assert($presenter instanceof IiifPresenter);
    ob_start();
    $response = $presenter->run(new Request('Front:Iiif', 'default', ["action"=>"manifest", "id"=>12]));
    $response->send((new RequestFactory())->fromGlobals(), new Response());

    $content = ob_get_clean();
    Assert::contains("Redirect", $content);
});

test('Front:Iiif:manifest:correct',function (): void {
    $container = Bootstrap::boot()->createContainer();

    $presenter = $container->getByName($container->findByType(IiifPresenter::class)[0]);
    assert($presenter instanceof IiifPresenter);
    ob_start();
    $response = $presenter->run(new Request('Front:Iiif', 'default', ["action"=>"manifest", "id"=>'prc_3810']));
    $response->send((new RequestFactory())->fromGlobals(), new Response());

    $content = ob_get_clean();
    Assert::contains("http://iiif.io/api/presentation/2/context.json", $content);
});
