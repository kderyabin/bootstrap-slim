<?php
/**
 * Copyright (c) 2018 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kod\BootstrapSlim\Tests\Mocks;

use Kod\BootstrapSlim\Middleware;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * MiddlewareMock writes some content before and after content generation.
 */
class MockMiddleware extends Middleware
{
    public static $contentBefore = 'BEFORE';
    public static $contentAfter = 'AFTER';
    /**
     * @param Request $request
     * @param Response $response
     * @param callable $next
     * @return Response
     */
    public function __invoke($request, $response, $next)
    {
        $response->getBody()->write(MockMiddleware::$contentBefore);
        $response = $next($request, $response);
        $response->getBody()->write(MockMiddleware::$contentAfter);

        return $response;
    }
}
