<?php

namespace Kod\BootstrapSlim\Tests;

use Kod\BootstrapSlim\Bootstrap;
use Kod\BootstrapSlim\Tests\Mocks\{MockBootstrap,
    MockGroupDefinitions,
    MockMiddleware,
    MockMiddlewareHandlerImplementation,
    MockRouteDefinitions};
use Kod\BootstrapSlim\Utils;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class MiddlewareDeclarationTest extends TestCase
{
    /**
     * @testdox Should be able to declare a middleware as function
     */
    public function testAddMiddlewareAsCallable()
    {
        Utils::setEnv([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/',
        ]);

        $bootstrap = (new MockBootstrap())
            ->addAppRoutes()
            ->addMiddleware(
                function ($request, RequestHandlerInterface $handler) {
                    $content = '*START*' . ((string)$handler->handle($request)->getBody()) . '*END*';

                    $response = new Response();
                    $response->getBody()->write($content);

                    return $response;
                }
            );

        $response = $bootstrap->run(true);
        $content = (string)$response->getBody();

        $this->assertContains('*START*', $content);
        $this->assertContains('*END*', $content);
        $this->assertContains(MockRouteDefinitions::$content, $content);
    }

    /**
     * @testdox Should be able to declare a middleware by function name
     */
    public function testAddMiddlewareByFunctionName()
    {
        Utils::setEnv([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/',
        ]);
        function middleware($request, RequestHandlerInterface $handler)
        {
            $content = '*START*' . ((string)$handler->handle($request)->getBody()) . '*END*';

            $response = new Response();
            $response->getBody()->write($content);

            return $response;
        }

        $bootstrap = (new MockBootstrap())
            ->addAppRoutes()
            ->addMiddleware(
                'Kod\BootstrapSlim\Tests\middleware'
            );

        $response = $bootstrap->run(true);
        $content = (string)$response->getBody();

        $this->assertContains('*START*', $content);
        $this->assertContains('*END*', $content);
        $this->assertContains(MockRouteDefinitions::$content, $content);
    }

    /**
     * @testdox Should be able to declare a middleware as an instance of some class
     */
    public function testAddMiddlewareAsInstance()
    {
        Utils::setEnv([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/',
        ]);

        $bootstrap = (new MockBootstrap())->addAppRoutes();
        $bootstrap->addMiddleware(
                new MockMiddleware($bootstrap->getContainer())
            );

        $response = $bootstrap->run(true);
        $content = (string)$response->getBody();

        $this->assertContains(MockMiddleware::$contentBefore, $content);
        $this->assertContains(MockMiddleware::$contentAfter, $content);
        $this->assertContains(MockRouteDefinitions::$content, $content);
    }

    /**
     * @testdox Should be able to declare a middleware as instance of MiddlewareInterface
     */
    public function testAddMiddlewareAsInstanceOfMiddlewareInterface()
    {
        Utils::setEnv([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/',
        ]);

        $bootstrap = (new MockBootstrap())->addAppRoutes();
        $bootstrap->addMiddleware(
                new MockMiddlewareHandlerImplementation($bootstrap->getContainer())
            );

        $response = $bootstrap->run(true);
        $content = (string)$response->getBody();

        $this->assertContains(MockMiddleware::$contentBefore, $content);
        $this->assertContains(MockMiddleware::$contentAfter, $content);
        $this->assertContains(MockRouteDefinitions::$content, $content);
    }

    /**
     * @testdox Should be able to declare a middleware by class name
     */
    public function testAddMiddlewareInterfaceClassByName()
    {
        Utils::setEnv([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/',
        ]);

        $bootstrap = (new MockBootstrap())->addAppRoutes();
        $bootstrap->addMiddleware(
                MockMiddlewareHandlerImplementation::class
            );

        $response = $bootstrap->run(true);
        $content = (string)$response->getBody();

        $this->assertContains(MockMiddleware::$contentBefore, $content);
        $this->assertContains(MockMiddleware::$contentAfter, $content);
        $this->assertContains(MockRouteDefinitions::$content, $content);
    }

    /**
     * @testdox Should be able to declare a middleware by class name
     */
    public function testAddMiddlewareByClassName()
    {
        Utils::setEnv([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/',
        ]);

        $bootstrap = $bootstrap = (new MockBootstrap())->addAppRoutes();
        $bootstrap->addMiddleware(
                MockMiddleware::class
            );

        $response = $bootstrap->run(true);
        $content = (string)$response->getBody();

        $this->assertContains(MockMiddleware::$contentBefore, $content);
        $this->assertContains(MockMiddleware::$contentAfter, $content);
        $this->assertContains(MockRouteDefinitions::$content, $content);
    }

    /**
     * @testdox Should ba able to declare a group with middleware
     */
    public function testGroupMiddleware()
    {
        $bootstrap = (new Bootstrap())->addRouteDefinitions(MockGroupDefinitions::class);

        Utils::setEnv([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/billing/',
        ]);

        $response = $bootstrap->run(true);
        $content = (string) $response->getBody();

        $this->assertContains('billing', $content);
        $this->assertContains(MockMiddleware::$contentBefore, $content);
        $this->assertContains(MockMiddleware::$contentAfter, $content);

        // Test invoice
        Utils::setEnv([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/billing/invoice/100',
        ]);
        $response = $bootstrap->run(true);
        $content = (string) $response->getBody();

        $this->assertContains('invoice', $content);
        $this->assertContains(MockMiddleware::$contentBefore, $content);
        $this->assertContains(MockMiddleware::$contentAfter, $content);
    }

    /**
     * @testdox Should ba able to declare a route middleware
     */
    public function testRouteMiddleware()
    {
        Utils::setEnv([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/',
        ]);
        $response = (new Bootstrap())
            ->addRouteDefinitions(function ($app) {
                $app->get('/', function (Request $request,Response $response) {
                    $response->getBody()->write('+home+');

                    return $response;
                })->add(MockMiddleware::class);
            })
            ->run(true);

        $content = (string) $response->getBody();

        $this->assertContains('home', $content);
        $this->assertContains(MockMiddleware::$contentBefore, $content);
        $this->assertContains(MockMiddleware::$contentAfter, $content);

    }

}
