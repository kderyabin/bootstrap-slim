<?php
/**
 * Copyright (c) 2018 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Kod\BootstrapSlim\Tests\Mocks;

use Kod\BootstrapSlim\RouteDefinitions;
use Slim\App;
use Slim\Psr7\{Request, Response};

/**
 * MockRouteDefinitions sets the route and content generation.
 */
class MockRouteDefinitions extends RouteDefinitions
{
    public static $content = ' BODY ';
    /**
     * @param App $app
     */
    public function __invoke($app)
    {
        $app->get('/', function (Request $request, Response $response) {
            $response->getBody()->write(MockRouteDefinitions::$content);

            return $response;
        });
    }
}
