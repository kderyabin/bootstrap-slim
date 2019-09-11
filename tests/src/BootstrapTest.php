<?php
/**
 * Copyright (c) 2018 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Kod\BootstrapSlim\Tests;

use Kod\BootstrapSlim\Utils;
use PHPUnit\Framework\TestCase;
use Kod\BootstrapSlim\Bootstrap;
use Kod\BootstrapSlim\Tests\Mocks\{MockBootstrap, MockContainer, MockMiddleware, MockRouteDefinitions};
use Psr\Container\ContainerInterface;
use Slim\App;


class BootstrapTest extends TestCase
{
    /**
     * @testdox Test initialisation
     */
    public function testConstruction()
    {
        $bootstrap = new Bootstrap([]);
        $this->assertInstanceOf(App::class, $bootstrap->getApp());
        $this->assertInstanceOf(ContainerInterface::class, $bootstrap->getContainer());
        $this->assertInstanceOf(Bootstrap::class, $bootstrap->addAppRoutes());
        $this->assertInstanceOf(Bootstrap::class, $bootstrap->addAppMiddleware());
    }

    /**
     * @testdox Test external container set up
     */
    public function testContainer()
    {
        $bootstrap = new Bootstrap(new MockContainer());
        $this->assertInstanceOf(MockContainer::class, $bootstrap->getContainer());
    }

    /**
     * @testdox Test customized Bootstrap class with application routes and middleware
     */
    public function testInitialisation()
    {
        $bootstrap = new MockBootstrap();
        $bootstrap->addAppMiddleware()->addAppRoutes();
        /**
         * @var App $app
         */
        $app = $bootstrap->getApp();
        $request = Utils::getRequest([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/',
        ]);
        $response = $app->handle($request);

        $content = (string)$response->getBody();

        $this->assertContains(MockMiddleware::$contentBefore, $content);
        $this->assertContains(MockMiddleware::$contentAfter, $content);
        $this->assertContains(MockRouteDefinitions::$content, $content);
    }

    /**
     * @testdox Should emit headers and output some content
     */
    public function testResponseOutput()
    {
        Utils::setEnv([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/',
        ]);

        ob_start();
        (new MockBootstrap())
            ->addAppRoutes()
            ->run(false);
        $content = ob_get_clean();
        $headers = getallheaders();

        $this->assertContains(MockRouteDefinitions::$content, $content);
        $this->assertNotEmpty($headers);
        $this->assertEquals('Slim Framework', $headers['User-Agent']);
    }


}
