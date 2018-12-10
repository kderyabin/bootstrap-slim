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
 * SampleMiddleware2 writes some content before and after content generation.
 */
class SampleMiddleware2 extends Middleware
{
    public static $contentBefore = '>>>route middleware';
    public static $contentAfter = '<<<route middleware';
    /**
     * @param Request $request
     * @param Response $response
     * @param callable $next
     * @return Response
     */
    public function __invoke($request, $response, $next)
    {
        $response->getBody()->write(static::$contentBefore);
        /**
         * @var Response $response
         */
        $response = $next($request, $response);
        $response->getBody()->write(static::$contentAfter);

        return $response;
    }
}
