<?php
/**
 * Copyright (c) 2018 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kod\BootstrapSlim\Tests;

use Kod\BootstrapSlim\Bootstrap;
use Kod\BootstrapSlim\Sample\SampleBootstrap;
use Kod\BootstrapSlim\Sample\SampleMiddleware;
use Kod\BootstrapSlim\Sample\SampleMiddleware2;
use Kod\BootstrapSlim\Sample\SampleRoute;
use Kod\BootstrapSlim\Sample\SampleRoute2;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

class BootstrapTest extends TestCase
{
    /**
     * @testdox  Test fluid pattern
     *
     */
    public function testConstruction()
    {
        $bootstrap = new Bootstrap(App::class, []);
        $this->assertInstanceOf(App::class, $bootstrap->getApp());
        $this->assertInstanceOf(ContainerInterface::class, $bootstrap->getContainer());
        $this->assertInstanceOf(Bootstrap::class, $bootstrap->addAppRoutes());
        $this->assertInstanceOf(Bootstrap::class, $bootstrap->addAppMiddleware());
        $this->assertInstanceOf(Bootstrap::class, $bootstrap->addAppDependencies());
    }

    /**
     * @testdox Test customized Bootstrap class with application RouteDefinitions and Middleware
     */
    public function testInitialisation()
    {
        // initialize with empty settings
        $bootstrap = new SampleBootstrap(App::class);
        $bootstrap->addAppMiddleware()->addAppRoutes();
        // prepare the request
        $container = $bootstrap->getContainer();
        $container['environment'] = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/',
        ]);
        // run the app
        $response = $bootstrap->run(true);
        $content = (string)$response->getBody();

        $this->assertContains(SampleMiddleware::$contentBefore, $content);
        $this->assertContains(SampleMiddleware::$contentAfter, $content);
        $this->assertContains(SampleRoute::$content, $content);
    }

    /**
     * @testdox Test Bootstrap initialization with callables.
     */
    public function testInitWithCallables()
    {
        $ci = [
            'environment' => Environment::mock([
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/',
            ]),
        ];

        $bootstrap = new Bootstrap(App::class, $ci);
        $bootstrap
            ->addMiddleware(
                new SampleMiddleware($bootstrap->getContainer()),
                function ($request, $response, $next) {
                    $response->getBody()->write('*START*');
                    $response = $next($request, $response);
                    $response->getBody()->write('*END*');

                    return $response;
                }
            )
            ->addRouteDefinitions(
                new SampleRoute()
            );

        $response = $bootstrap->run(true);
        $content = (string)$response->getBody();

        $this->assertContains(SampleMiddleware::$contentBefore, $content);
        $this->assertContains(SampleMiddleware::$contentAfter, $content);
        $this->assertContains('*START*', $content);
        $this->assertContains('*END*', $content);

        $this->assertContains(SampleRoute::$content, $content);
    }

    /**
     * @testdox Must throw an exception if middleware is of a bad type
     */
    public function testAddMiddlewareBadType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $bootstrap = new Bootstrap(App::class);

        $bootstrap->addMiddleware([]);
    }

    /**
     * @testdox Must throw an exception if route is of a bad type
     */
    public function testAddRouteBadType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $bootstrap = new Bootstrap(App::class);

        $bootstrap->addRouteDefinitions([]);
    }

    /**
     * @testdox Should return a route's URI
     */
    public function testRouteUriGeneration()
    {
        $bootstrap = new Bootstrap(App::class, []);
        $bootstrap->addRouteDefinitions(function ($app) {
            $app->get('/hello/{name}', function ($request, $response, $args) {
                // Show book identified by $args['id']
            })->setName('hello');
        }
        );
        $uri = $bootstrap->getPathFor('hello', ['name' => 'John']);
        $this->assertEquals('/hello/John', $uri);
    }

    /**
     * @testdox Should execute a route's middleware and app middleware
     */
    public function testRouteMiddleware()
    {
        // prepare the request
        $conf = [
            'environment' => Environment::mock([
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/route2',
            ]),
        ];
        // initialize the app
        $bootstrap = new SampleBootstrap(App::class, $conf);
        $bootstrap->addAppMiddleware()->addAppRoutes();
        /**
         * @var Response $response
         */
        $response = $bootstrap->run(true);
        $content = (string)$response->getBody();
        // test the route's middleware output
        $this->assertContains(SampleMiddleware2::$contentBefore, $content);
        $this->assertContains(SampleMiddleware2::$contentAfter, $content);
        // test the app middleware output
        $this->assertContains(SampleMiddleware::$contentBefore, $content);
        $this->assertContains(SampleMiddleware::$contentAfter, $content);
        // test the content
        $this->assertContains(SampleRoute2::$content, $content);
    }

    public function testDependencyInjection()
    {
        $bootstrap = new SampleBootstrap(App::class, []);
        $bootstrap
            ->addAppDependencies()
            ->addRouteDefinitions(function ($app) {
                $app->get('/', function (Request $request, Response $response) {
                    /**
                     * @var ContainerInterface $this
                     */
                    $formatName = $this->get('person');
                    return $response->write($formatName('John Doe'));
                });
            });
        $env = [
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/',
        ];
        $request = static::getRequest($env);
        $response = $bootstrap->getApp()->process($request, new Response());
        $content =(string)$response->getBody();
        $this->assertEquals('Person name: John Doe', $content);
    }


    public function testPostFormData()
    {
        $bootstrap = new Bootstrap(App::class, []);
        $bootstrap
            ->addRouteDefinitions(function ($app) {
                $app->post('/', function (Request $request, Response $response) {
                    return $response->write(
                        print_r($request->getParsedBody(), true)
                    );
                });
            });
        $env = [
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI' => '/',
        ];
        $request = (static::getRequest($env))
            ->withParsedBody([
                'name' => 'John',
                'surname' => 'Doe'
            ]);
        $response = $bootstrap->getApp()->process($request, new Response());
        $content =(string)$response->getBody();
        $this->assertContains('John', $content);
        $this->assertContains('Doe', $content);
    }

    /**
     * @param array $env
     * @return ServerRequestInterface
     */
    protected static function getRequest(array $env): ServerRequestInterface
    {
        return Request::createFromEnvironment(
            Environment::mock($env)
        );
    }
}
