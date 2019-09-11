# Bootstrap SLIM  

This package provides a library for bootstrapping Slim framework 4 application.
(Release 1 is for Slim 3) 

## Purpose

- Centralize the application's initialisation
- Reuse existing configuration in unit tests
- Create on the fly new customized configuration for unit tests
- Reduce usage of `require`/`include` statements in favour of `use` statement 


## Installation

Add this package to `require` section of the `composer.json`

```
composer require kod/bootstrap-slim
```



## Implementation in 3 steps


### Bootstrap  - step 1

On `index.php` for handling public requests.
```php
<?php
use MyProject\Bootstrap;

// application configuration
$config = require('config.php');

 (new Bootstrap($config))
        ->addAppRoutes()
        ->addAppMiddlewares()
        ->run();
```

Of course, `Bootstrap` class does not provide any route or middleware except native Slim RoutingMiddleware and ErrorMiddleware.
You need to extend it and declare your routes in `Bootstrap::addAppRoutes()` and middleware stack in 
`Bootstrap::addAppMiddlewares()` methods. It can be done easily by declaring class names like in example below.

### Declare application routes and middleware stack

```php
<?php
namespace MyProject;

use Kod\BootstrapSlim\Bootstrap as SlimBootstrap;

class Bootstrap extends SlimBootstrap 
{
    public function addAppRoutes()
    {
        return $this->addRouteDefinitions(
            HomeRoutes::class,
            HelpRoutes::class
        );
    }
    public function addAppMiddleware()
    {
        return $this->addMiddleware(
            ValidateResponse::class,
            SecurityHeaders::class,
            ValidateRequest::class
        );
    }
}
```

None of those classes exists yet. So, let's start with the most important. Let's create our route. 

### Routing - step 2

You have 2 options for creating routes. If you are a fun of OOP you can create a class containing your routes by 
extending `RouteDefintions` abstract class and declare your routes in `__invoke($app)` method of this class. As 
said above, you can but you are not obliged to. The MUST is to implement the `__invoke($app)` method in your class which 
receives `Slim\App` instance as a parameter. The instance of the class is executed like a function. 

And here comes the 2nd option. You can declare your routes as Closure which gets `Slim\App` instance as a parameter. The 2nd approach is quite 
useful for unit tests if you need to create a route on the fly. But what am I saying? I'm getting obsolete. With php we can 
create anonymous classes! ¡Ay, caramba!

Anyway, just keep in mind that in both cases the `Slim\App` instance is passed as a parameter. That means you can 
organize your routes in groups and attach middleware to some route or group.

#### Route as a class: 
```php
<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Kod\BootstrapSlim\RouteDefinitions;

class HomesRoutes extends RouteDefinitions
{
    public function __invoke($app)
    {
        $app->get('/', function (Request $request, Response $response, $args) {
            $response->getBody()->write('home page');

            return $response;
        });
    }
}
```

#### Application routes' declaration as a Closure
```php
<?php
namespace MyProject;

use Kod\BootstrapSlim\Bootstrap as SlimBootstrap;
use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use MyProject\Routes\HomesRoutes;

class Bootstrap extends SlimBootstrap 
{
    public function addAppRoutes()
    {
        return $this->addRouteDefinitions(
            HomesRoutes::class,
            function(App $app){
               $app->get('/', function (Request $request, Response $response, $args) {
                   return $response;
               });
            }
        );
    }
}
```

### Middleware - step 3

You can create a middleware in 2 ways: as a class or a closure. To create it as a class simply extend 
invokable `Middleware` abstract class, implement your business logic in it and trigger its execution in 
`Middleware::__invoke( ServerRequestInterface $request,RequestHandlerInterface $handler)` method. 

Slim 4 middleware implements PSR-15 Middleware Interface which means that you do not have to use `Middleware` abstract 
class but write your own implementing the PSR-15 Middleware Interface. That'll do the job as well. 

Either way, the application container will be pushed into the constructor of the middleware class by 
`Slim\MiddlewareDispatcher`.In `Middleware` derived classes the container will be stocked in `$this->ci` property.

To create a middleware as a closure simply respect the function signature.

#### Middleware as invokable class
```php
<?php

use Kod\BootstrapSlim\Middleware;
use Psr\Http\{
    Message\ResponseInterface,
    Message\ServerRequestInterface,
};
use Psr\Http\Server\RequestHandlerInterface;

/**
 * MiddlewareMock writes some content before and after content generation.
 */
class MyMiddleware extends Middleware
{
    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface  $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $container = $this->ci; // absolutely useless, just for the demo
        $response = $handler->handle($request);
        ... // some treatment here
        return $response;
    }
}
```

#### Application middleware declaration
```php
<?php
namespace MyProject;

use Kod\BootstrapSlim\Bootstrap as SlimBootstrap;
use Psr\Http\{
    Message\ResponseInterface,
    Message\ServerRequestInterface,
};
use Psr\Http\Server\RequestHandlerInterface;
use MyProject\Middleware\MyMiddleware;

class Bootstrap extends SlimBootstrap 
{
    public function addAppMiddleware()
    {
        return $this->addMiddleware(
            MyMiddleware::class,
            function (ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
                $container = $this; //bound by Slim\MiddlewareDispatcher
                $response = $handler->handle($request);
                ... // some treatment here
                return $response;
           }
        );
    }
}
```

#### Route/Group middleware

Slim allows to attach a middleware to a route or a group of routes. This can be done in routes' definition like in 
example below. 

```php
<?php

use Kod\BootstrapSlim\RouteDefinitions;
use Slim\Psr7\{Request, Response};
use Slim\Interfaces\RouteCollectorProxyInterface;
use MyProject\Middleware\MyMiddleware;

class BillingRoutes extends RouteDefinitions
{
    /**
     * @param App $app
     */
    public function __invoke($app)
    {
        $app->group('/billing', function (RouteCollectorProxyInterface $group) {
            $group->get('/', function (Request $request, $response, $args) {
                // Route for /billing
                ... // some treatment
                return $response;
            });

            $group->get('/invoice/{id:[0-9]+}', function (Request $request, Response $response, $args) {
                // Route for /invoice/{id:[0-9]+}
                ... // some treatment
                return $response;
            });
        })->add(MyMiddleware::class);
    }
}

```


## Container

Slim 4 has a breaking change. It does not have container anymore. But no worry! This package provides an implementation of
`ContainerInterface` built on top of `Pimple\Container` with same methods as in Slim 3. If your application relies on some other 
container implementation simply provide the instance of your container to Bootstrap. Another way to customize the container
is to override `Bootstrap::init` method in your Bootstrap class and declare it in there.

```php
<?php
use MyProject\Bootstrap;
use MyProject\MyContainer;

// application configuration
$config = require('config.php');
$container = new MyContainer($config);

(new Bootstrap($container))
        ->addAppRoutes()
        ->addAppMiddlewares()
        ->run();

``` 

## Unit Testing

The keyword in unit testing is UNIT. Once your application is well organized and weakly coupled unit testing is like 
playing LEGO with your code. You can assemble routes and middleware, declare them on the fly as you wish.

```php
<?php

use MyProject\{MyBootstrap, MyRoute};
use Kod\BootstrapSlim\Utils;

class MyRouteTest extends TestCase
{
    // test configuration
    public static $config = [ 'settings' => ['price' => 100] ];

    public function testMyRouteWithoutAppMiddleware()
    {
        // Prepare environment for the request
         Utils::setEnv([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/',
         ]);
        // Process the request
        $response = (new MyBootstrap(static::$config))
            ->addRouteDefinitions(MyRoute::class)
            ->run(true);
       
        $content = (string)$response->getBody();
        // MyRoute class is supposed to return the price from settings  
        $this->assertContains(100, $content);
    }
    
    public function testMyRouteWithAppMiddleware()
    {
        // Prepare environment for the request
         Utils::setEnv([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/',
         ]);
        // Process the request
        $response = (new MyBootstrap(static::$config))
            ->addAppMiddleware() // <- declare application middleware
            ->addRouteDefinitions(MyRoute::class)
            ->run(true);
       
        $content = (string)$response->getBody();
        // MyRoute class is supposed to return the price from settings  
        $this->assertContains(100, $content);
    }

}

```

## Slim dependency

Slim framework 4 now makes part of project dependencies due to quite sophisticated request/response forgery introduced 
with the latest version. To get rid of that dependency and rely on the slim version installed on your project you need
to override `Bootstrap::init` method and use `AppFactory` and `ServerRequestCreatorFactory` coming with your project. 
