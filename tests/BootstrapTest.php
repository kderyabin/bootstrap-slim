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

namespace MDCS\Slim\Tests;

use MDCS\Slim\Bootstrap;
use MDCS\Slim\Tests\Mocks\MockBootstrap;
use MDCS\Slim\Tests\Mocks\MockMiddleware;
use MDCS\Slim\Tests\Mocks\MockRoutes;
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
        $this->assertInstanceOf(ContainerInterface::class, $bootstrap->getAppContainer());
    }

    /**
     * Test customized Bootstrap class with default Routes and Middleware set up.
     * @throws \Exception
     * @throws \Slim\Exception\MethodNotAllowedException
     * @throws \Slim\Exception\NotFoundException
     */
    public function testInitialisation()
    {
        $bootstrap = new MockBootstrap(App::class);
        $bootstrap->addDefaultMiddlewares()->addDefaultRoutes();
        /**
         * @var App $app
         */
        $app = $bootstrap->getApp();
        $request = static::getRequest();
        $response = $app->process( $request, new Response());

        $content = (string)$response->getBody();

        $this->assertContains(MockMiddleware::$contentBefore, $content);
        $this->assertContains(MockMiddleware::$contentAfter, $content);
        $this->assertContains(MockRoutes::$content, $content);
    }

    /**
     * @return ServerRequestInterface
     */
    public static function getRequest()
    {
        $env = array(
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/',
        );

        return Request::createFromEnvironment(Environment::mock($env));
    }
}
