<?php
/**
 * Copyright (c) 2018 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kod\BootstrapSlim\Sample;

use Kod\BootstrapSlim\Middleware;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * MiddlewareMock writes some content before and after content generation.
 */
class SampleMiddleware extends Middleware
{
    public static $contentBefore = '>>>app middleware';
    public static $contentAfter = '<<<app middleware';
    /**
     * @param Request $request
     * @param Response $response
     * @param callable $next
     * @return Response
     */
    public function __invoke($request, $response, $next)
    {
        $response->getBody()->write(static::$contentBefore);
        $response = $next($request, $response);
        /**
         * @var Response $response
         */
        $response->getBody()->write(static::$contentAfter);

        return $response;
    }
}
