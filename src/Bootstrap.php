<?php
/**
 * Copyright (C)  2018 Orange
 *
 * This software is confidential and proprietary information of Orange.
 * You shall not disclose such Confidential Information and shall use it only
 * in accordance with the terms of the agreement you entered into.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 */

namespace MDCS\Slim;

use Psr\Container\ContainerInterface;

/**
 * Bootstrap
 */
class Bootstrap
{
    /**
     * @var object Slim App instance.
     */
    protected $app;

    /**
     * Bootstrap constructor.
     * @param string $slimClass Fully qualified Slim\App class name.
     * @param array $slimSettings Slim settings.
     */
    public function __construct(string $slimClass, $slimSettings = [])
    {
        $this->app = new $slimClass($slimSettings);
    }

    /**
     * Attach application middlewares.
     * Override this method and declare your middlewares here.
     * The middleware definition order is important.
     * @return static
     */
    public function addDefaultMiddlewares()
    {
        return $this;
    }

    /**
     * Attach application routes.
     * Override this method and declare your route(s) here.
     * @return static
     */
    public function addDefaultRoutes()
    {
        return $this;
    }

    /**
     * Add app middleware(s).
     * The only requirement: $middleware can be a callable or a string for a class name.
     * A $middleware may not be necessarily an instance of MDCS\Slim\Middleware.
     * @param mixed $middleware ... Unlimited number of callable middleware.
     * @return static
     */
    public function addMiddleware(...$middleware)
    {
        for ($i = 0; $i < count($middleware); $i++) {
            if (is_callable($middleware[$i])) {
                $this->app->add($middleware[$i]);
            } else {
                $class = $middleware[$i];
                $callable = new $class($this->getAppContainer());
                $this->app->add($callable);
            }
        }

        return $this;
    }

    /**
     * Add application route(s).
     * The only requirement: $routes can be a callable or a string for a class name.
     * A $routes parameter may not be necessarily an instance of MDCS\Slim\Routes.
     * @param $routes ...    Unlimited number of routes definitions.
     * @return static
     */
    public function addRoutes(...$routes)
    {
        for ($i = 0; $i < count($routes); $i++) {
            if (is_callable($routes[$i])) {
                $routes[$i]($this->app);
            } else {
                $class = $routes[$i];
                $callable = new $class();
                $callable($this->app);
            }
        }

        return $this;
    }

    /**
     * Get Slim App instance.
     * @return object
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * Get Slim container with app settings.
     * @return ContainerInterface
     */
    public function getAppContainer(): ContainerInterface
    {
        return $this->app->getContainer();
    }

    /**
     * Execute Slim app.
     */
    public function run()
    {
        $this->app->run();
    }
}
