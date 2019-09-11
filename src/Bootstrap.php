<?php
/**
 * Copyright (c) 2018 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Kod\BootstrapSlim;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Slim\App;
use Slim\Factory\{AppFactory, ServerRequestCreatorFactory};
use Slim\Interfaces\ServerRequestCreatorInterface;
use InvalidArgumentException;

/**
 * Bootstrap
 */
class Bootstrap
{
    /**
     * @var App
     */
    protected $app;
    /**
     * @var ServerRequestCreatorInterface
     */
    protected $serverRequestCreator;

    /**
     * Bootstrap constructor.
     * @param array|ContainerInterface  array of settings or instance of ContainerInterface
     */
    public function __construct($settings = [])
    {
        $this->init($settings);
    }

    /**
     * Override to implement your app initialisation logic
     * @param array|ContainerInterface  array of settings or instance of ContainerInterface
     */
    public function init($settings = [])
    {
        $container = $settings instanceof ContainerInterface ? $settings : new Container($settings);
        AppFactory::setContainer($container);
        $this->app = AppFactory::create();
        $this->app->addRoutingMiddleware();
        $this->app->addErrorMiddleware(false, true, true);
        $this->serverRequestCreator = ServerRequestCreatorFactory::create();
    }

    /**
     * Declare application middleware stack.
     * The middleware definition order is important.
     * Override this method and declare your middleware here.
     *
     * @return static   Bootstrap instance
     *
     * @example
     *
     *  public function addAppMiddleware()
     *  {
     *      return $this->addMiddleware(
     *          FirstMiddleware::class,
     *          SecondMiddleware::class
     *      )
     *   }
     */
    public function addAppMiddleware()
    {
        return $this;
    }

    /**
     * Attach application routes
     * Override this method and declare your route(s) here.
     * @return static   Bootstrap instance
     *
     * @example
     *
     * public function addAppRoutes(){
     *      return $this->addRouteDefinitions(
     *          SomeRoute::class,
     *          SomeOtherRoute::class
     *      );
     * }
     */
    public function addAppRoutes()
    {
        return $this;
    }

    /**
     * Add application middleware
     * The only requirement: $middleware must be a callable or a string for a class name.
     * A $middleware may not be necessarily an instance of Kod\BootstrapSlim\Middleware.
     *
     * @param array ...$middleware
     * @return static
     *
     * @example
     *
     * (new Bootstrap())->addMiddleware(
     *      FirstMiddleware::class,
     *      SecondMiddleware::class,
     *      function($request, $handler){
     *          $response = $handler->process($request);
     *          ...
     *          return $response;
     *      }
     *  );
     */
    public function addMiddleware(...$middleware)
    {
        foreach ($middleware as $mw) {
            if (is_callable($mw)) {
                $this->app->add($mw);
                continue;
            }

            if ($mw instanceof MiddlewareInterface) {
                $this->app->addMiddleware($mw);
                continue;
            }

            if (is_string($mw)) {
                $this->app->add($mw);
                continue;
            }
        }

        return $this;
    }

    /**
     * Add application route(s).
     * The only requirement: $routes must be a callable or a string for a class name.
     * A $routes parameter may not be necessarily an instance of Kod\BootstrapSlim\RouteDefinitions.
     * @param $routes ...    Unlimited number of routes definitions.
     * @return static
     * @throws InvalidArgumentException
     *
     * @example
     *
     * (new Bootstrap())->addRouteDefinitions(
     *      SomeRoute::class,
     *      SomeOtherRoute::class,
     *      function($app){
     *          $app->get('/', function($request, $response; $args){
     *              return new Response();
     *          )->setName('home');
     *      }
     *  );
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
                throw new InvalidArgumentException('Unsupported type for the route');
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
     * Get application container.
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->app->getContainer();
    }

    /**
     * @param bool $silent FALSE to output the content and emit header, TRUE to return generated response
     * @return ResponseInterface|null
     */
    public function run(bool $silent = false): ?ResponseInterface
    {
        if (!$silent) {
            $this->app->run();
            return null;
        }

        $request = $this->serverRequestCreator->createServerRequestFromGlobals();

        return $this->app->handle($request);
    }

    /**
     * Shortcut for the named route URL.
     *
     * @param string $name
     * @param array $data
     * @param array $queryParams
     * @return string
     */
    public function getPathFor(string $name, array $data = [], array $queryParams = []): string
    {
        return $this->app->getRouteCollector()->getRouteParser()->urlFor($name, $data, $queryParams);
    }
}
