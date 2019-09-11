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
use Slim\Interfaces\RouteCollectorProxyInterface;

/**
 * Group defintion with group middleware
 * @package Kod\BootstrapSlim\Tests\Mocks
 */
class MockGroupDefinitions extends RouteDefinitions
{
    public static $content = ' BODY ';
    /**
     * @param App $app
     */
    public function __invoke($app)
    {
        $app->group('/billing', function (RouteCollectorProxyInterface $group) {
            $group->get('/', function (Request $request, Response $response, $args) {
                // Route for /billing
                $response->getBody()->write(' billing ');

                return $response;
            });

            $group->get('/invoice/{id:[0-9]+}', function (Request $request, Response $response, $args) {
                // Route for /invoice/{id:[0-9]+}
                $response->getBody()->write(' invoice ');

                return $response;
            });
        })->add(MockMiddleware::class);
    }
}
