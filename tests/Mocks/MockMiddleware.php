<?php
/**
 * Copyright (c) 2018 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Kod\BootstrapSlim\Tests\Mocks;

use Kod\BootstrapSlim\Middleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

/**
 * MiddlewareMock writes some content before and after content generation.
 */
class MockMiddleware extends Middleware
{
    public static $contentBefore = 'BEFORE';
    public static $contentAfter = 'AFTER';

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface  $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $existingContent = (string) $response->getBody();

        $response = new Response();
        $response->getBody()->write(
            static::$contentBefore .
            $existingContent .
            static::$contentAfter
        );

        return $response;
    }
}
