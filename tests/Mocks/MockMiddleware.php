<?php
/**
 * Copyright (C)  2018 Orange
 *
 * This software is confidential and proprietary information of Orange.
 * You shall not disclose such Confidential Information and shall use it only
 * in accordance with the terms of the agreement you entered into.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 */
/**
 * Created on 2018-08-07
 */

namespace MDCS\Slim\Tests\Mocks;

use MDCS\Slim\Middleware;
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
