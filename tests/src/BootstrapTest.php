<?php
/**
 * Copyright (c) 2018 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kod\BootstrapSlim\Tests;

use Kod\BootstrapSlim\Bootstrap;
use Kod\BootstrapSlim\Tests\Mocks\MockBootstrap;
use Kod\BootstrapSlim\Tests\Mocks\MockMiddleware;
use Kod\BootstrapSlim\Tests\Mocks\MockRouteDefinitions;
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
     * Test base class
     */
    public function testConstruction()
    {
        $bootstrap = new Bootstrap(App::class, []);
        $this->assertInstanceOf(App::class, $bootstrap->getApp());
        $this->assertInstanceOf(ContainerInterface::class, $bootstrap->getContainer());
        $this->assertInstanceOf(Bootstrap::class, $bootstrap->addAppRoutes());
        $this->assertInstanceOf(Bootstrap::class, $bootstrap->addAppMiddleware());
    }

    /**
     * Test customized Bootstrap class with default RouteDefinitions and Middleware set up.
     * @throws \Exception
     * @throws \Slim\Exception\MethodNotAllowedException
     * @throws \Slim\Exception\NotFoundException
     */
    public function testInitialisation()
    {
        $bootstrap = new MockBootstrap(App::class);
        $bootstrap->addAppMiddleware()->addAppRoutes();
        /**
         * @var App $app
         */
        $app = $bootstrap->getApp();
        $request = static::getRequest(static::getEnv([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/',
        ]));
        $response = $app->process($request, new Response());

        $content = (string)$response->getBody();

        $this->assertContains(MockMiddleware::$contentBefore, $content);
        $this->assertContains(MockMiddleware::$contentAfter, $content);

        $this->assertContains(MockRouteDefinitions::$content, $content);
    }

    /**
     * @testdox Test Bootstrap initialization with callables.
     */
    public function testInitWithCallables()
    {
        $ci = [
            'environment' => static::getEnv([
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/',
            ]),
        ];

        $bootstrap = new MockBootstrap(App::class, $ci);
        $bootstrap
            ->addMiddleware(
                new MockMiddleware($bootstrap->getContainer()),
                function ($request, $response, $next) {
                    $response->getBody()->write('*START*');
                    $response = $next($request, $response);
                    $response->getBody()->write('*END*');

                    return $response;
                }
            )
            ->addRouteDefinitions(
                new MockRouteDefinitions()
            );

        $response = $bootstrap->run(true);
        $content = (string)$response->getBody();

        $this->assertContains(MockMiddleware::$contentBefore, $content);
        $this->assertContains(MockMiddleware::$contentAfter, $content);
        $this->assertContains('*START*', $content);
        $this->assertContains('*END*', $content);

        $this->assertContains(MockRouteDefinitions::$content, $content);
    }

    /**
     * @testdox Must throw an exception if middleware is of a bad type
     */
    public function testAddMiddlewareBadType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $bootstrap = new MockBootstrap(App::class);

        $bootstrap->addMiddleware( []);
    }

    /**
     * @testdox Must throw an exception if route is of a bad type
     */
    public function testAddRouteBadType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $bootstrap = new MockBootstrap(App::class);

        $bootstrap->addRouteDefinitions( []);
    }

    /**
     * @testdox Should return return a route's URI
     */
    public function testRouteUriGeneration()
    {
        $bootstrap = new MockBootstrap(App::class, []);
        $bootstrap->addRouteDefinitions(  function($app) {
            $app->get('/hello/{name}', function ($request, $response, $args) {
                // Show book identified by $args['id']
            })->setName('hello');
            }
        );
        $uri = $bootstrap->getPathFor('hello', [ 'name' => 'John']);
        $this->assertEquals('/hello/John', $uri);
    }

    /**
     * @param Environment $env
     * @return ServerRequestInterface
     */
    protected static function getRequest( Environment $env ): ServerRequestInterface
    {
        return Request::createFromEnvironment($env);
    }

    /**
     * @param array $data
     * @return Environment
     */
    protected static function getEnv( array $data): Environment
    {
        return Environment::mock($data);
    }
}
