<?php

namespace Kod\BootstrapSlim\Tests;

use Kod\BootstrapSlim\Bootstrap;
use Kod\BootstrapSlim\Tests\Mocks\MockBootstrap;
use Kod\BootstrapSlim\Utils;
use PHPUnit\Framework\TestCase;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use InvalidArgumentException;

class RoutesDeclarationTest extends TestCase
{

    /**
     * @testdox Must throw an exception if route is of a bad type
     */
    public function testAddRouteBadType()
    {
        $this->expectException(InvalidArgumentException::class);
        (new MockBootstrap())->addRouteDefinitions([]);
    }

    /**
     * @testdox Should ba able to declare a route as Closure
     */
    public function testAddRouteAsClosure()
    {
        Utils::setEnv([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/',
        ]);;
        //
        $response = (new Bootstrap())
            ->addRouteDefinitions(
                function (App $app) {
                    $app->get('/', function (Request $request, Response $response, $args) {
                        $body = $response->getBody();
                        $body->write('home');
                        return $response->withBody($body);

                    });
                }
            )->run(true);

        $this->assertEquals('home', (string)$response->getBody());
    }

    /**
     * @testdox Should return return a route's URI
     */
    public function testRouteUriGeneration()
    {
        $bootstrap = (new MockBootstrap())->addRouteDefinitions(
            function ($app) {
                $app->get('/hello/{name}', function ($request, $response, $args) {
                    return new Response();
                })->setName('hello');
            }
        );
        $uri = $bootstrap->getPathFor('hello', ['name' => 'John']);
        $this->assertEquals('/hello/John', $uri);
    }

    /**
     * @testdox Should ba able to declare a group
     */
    public function testGroup()
    {
        $bootstrap = (new Bootstrap())->addRouteDefinitions(
            function (App $app) {
                $app->group('/billing', function (RouteCollectorProxyInterface $group) {
                    $group->get('/', function ($request, $response, $args) {
                        // Route for /billing
                        $response->getBody()->write('billing');

                        return $response;
                    });

                    $group->get('/invoice/{id:[0-9]+}', function ($request, $response, $args) {
                        // Route for /invoice/{id:[0-9]+}
                        $response->getBody()->write('invoice');

                        return $response;
                    });
                }
                );
            }
        );
        // Test billing route
        Utils::setEnv([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/billing/',
        ]);
        $response = $bootstrap->run(true);

        $this->assertEquals('billing', (string)$response->getBody());

        // Test invoice
        Utils::setEnv([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/billing/invoice/100',
        ]);
        $response = $bootstrap->run(true);
        $this->assertEquals('invoice', (string)$response->getBody());
    }
}
