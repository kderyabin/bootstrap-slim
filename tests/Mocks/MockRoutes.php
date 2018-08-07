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

use MDCS\Slim\Routes;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * MockRoutes sets the route and content generation.
 */
class MockRoutes extends Routes
{
    public static $content = ' BODY ';
    /**
     * @param App $app
     */
    public function __invoke($app)
    {
        $app->get('/', function (Request $request, Response $response) {
            $response->getBody()->write(MockRoutes::$content);

            return $response;
        });
    }
}
