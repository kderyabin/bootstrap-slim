<?php
/**
 * Copyright (c) 2018 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kod\BootstrapSlim\Sample;

use Kod\BootstrapSlim\RouteDefinitions;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * SampleRoute sets the route and content generation.
 */
class SampleRoute extends RouteDefinitions
{
    public static $content = ' SampleRoute ';
    /**
     * @param App $app
     */
    public function __invoke($app)
    {
        $app->get('/', function (Request $request, Response $response) {
            $response->getBody()->write(SampleRoute::$content);

            return $response;
        });
    }
}
