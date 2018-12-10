<?php
/**
 * Copyright (c) 2018 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kod\BootstrapSlim;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

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
     * Attach application middleware.
     * Override this method and declare your middlewares here.
     * The middleware definition order is important.
     * @return static
     * @example
     * public function addAppMiddleware()
     * {
     *      $this->addMiddleware(
     *          MyMiddleware::class,
     *          MyAnotherMiddleware::class
     *      );
     *      return $this;
     * }
     */
    public function addAppMiddleware()
    {
        return $this;
    }

    /**
     * Attach application routes.
     * Override this method and declare your route(s) here.
     * @return static
     * @example
     * public function addAppRoutes()
     * {
     *      $this->addRouteDefinitions(
     *          MyRoute::class,
     *          MyAnotherRoute::class
     *      );
     *      return $this;
     * }
     */
    public function addAppRoutes()
    {
        return $this;
    }

    /**
     * Inject application dependencies.
     * Override this method and declare your DI here.
     * @return static
     * @example
     * public function addAppDependencies()
     * {
     *      $container = $this->getContainer();
     *      // for pimple based container
     *      $container['session'] = $container->factory(function ($c) {
     *          return new Session($c['session_storage']);
     *      });
     *      return $this;
     * }
     */
    public function addAppDependencies()
    {
        return $this;
    }

    /**
     * Add application middleware
     * The only requirement: $middleware can be a callable or a string for a class name.
     * A $middleware may not be necessarily an instance of Kod\BootstrapSlim\Middleware.
     *
     * @param array ...$middleware
     * @return static
     * @throws \InvalidArgumentException
     */
    public function addMiddleware(...$middleware)
    {
        foreach ($middleware as $mw) {
            if (is_callable($mw)) {
                $this->app->add($mw);
            } elseif (is_string($mw)) {
                $class = $mw;
                $callable = new $class($this->getContainer());
                $this->app->add($callable);
            } else {
                throw new \InvalidArgumentException('Unsupported type for the middleware');
            }
        }

        return $this;
    }

    /**
     * Add application route(s).
     * The only requirement: $routes can be a callable or a string for a class name.
     * A $routes parameter may not be necessarily an instance of Kod\BootstrapSlim\RouteDefinitions.
     * @param $routes ...    Unlimited number of routes definitions.
     * @return static
     * @throws \InvalidArgumentException
     */
    public function addRouteDefinitions(...$routes)
    {
        foreach ($routes as $route) {
            if (is_callable($route)) {
                $route($this->app);
            } elseif (is_string($route)) {
                $callable = new $route();
                $callable($this->app);
            } else {
                throw new \InvalidArgumentException('Unsupported type for the route');
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
    public function getContainer(): ContainerInterface
    {
        return $this->app->getContainer();
    }

    /**
     * Wrapper for Slim Ap::run() mrthod
     */
    /**
     * @param bool $silent
     * @return ResponseInterface
     */
    public function run(bool $silent = false)
    {
        return $this->app->run($silent);
    }

    /**
     * Shortcut for the URL generation of the named route
     * with the Slim application router’s pathFor() method
     * @param string $name
     * @param array $args
     * @return string
     */
    public function getPathFor(string $name, array $args): string
    {
        return $this->app->getContainer()->get('router')->pathFor($name, $args);
    }
}
