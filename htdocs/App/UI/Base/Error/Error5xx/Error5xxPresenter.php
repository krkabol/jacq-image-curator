<?php declare(strict_types = 1);

namespace App\UI\Base\Error\Error5xx;

use Nette\Application\IPresenter;
use Nette\Application\Request;
use Nette\Application\Response;
use Nette\Application\Responses\CallbackResponse;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Tracy\ILogger;

/**
 * Handles uncaught exceptions and errors, and logs them.
 */
final class Error5xxPresenter implements IPresenter
{

    public function __construct(
        private ILogger $logger,
    )
    {
    }

    public function run(Request $request): Response
    {
        // Log the exception
        $exception = $request->getParameter('exception');
        $this->logger->log($exception, ILogger::EXCEPTION);

        // Display a generic error message to the user
        return new CallbackResponse(function (IRequest $httpRequest, IResponse $httpResponse): void {
            if (preg_match('#^text/html(?:;|$)#', (string) $httpResponse->getHeader('Content-Type'))) {
                require __DIR__ . '/500.phtml';
            }
        });
    }

}
