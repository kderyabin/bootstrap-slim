# Bootstrap SLIM  3

This repository holds a library for bootstrapping SLIM 3 application.

## Purpose

- Centralize the application's initialisation
- Reuse existing configuration in unit tests
- Create on the fly a new customized configuration for unit tests
- Reduce usage of `require`/`include` statements.

## What's in the library

| File | Description  |
|:---|:--- |
|`Middleware.php` | Abstract class for creating a middleware.|
|`RouteDefintions.php` | Abstract class for declaring route's definitions| 
|`Bootstrap.php` | Main class allowing to declare middleware and routes and run the Slim application| 

## Usage:

### How to implement

Add this package to `require` section of the `composer.json`
```
composer require kod/bootstrap-slim
```
Create a class containing your routes by extending `Kod\BootstrapSlim\RouteDefintions` abstract class and declare your routes 
in `__invoke($app)` method.
```php
<?php
use Kod\BootstrapSlim\RouteDefinitions;

class TestRouteDefinitions extends RouteDefinitions
{
    /**
     * @param App $app  Slim App class instance
     */
    public function __invoke($app)
    {
        $app->get('/', function ($request, $response) {
            $response->getBody()->write(MockRouteDefinitions::$content);

            return $response;
        });
    }
}
```
Create your middleware class(es), if you use them, by extending  `Kod\BootstrapSlim\Middleware` abstract class
and implement your business logic in `__invoke()` method.<br/>
Create a Bootstrap class by extending `Kod\BootstrapSlim\Bootstrap` class. <br/>
Override `Bootstrap::addAppRoutes` and add your Routes class there.<br/>
Override `Bootstrap::addAppMiddleware` and add your middleware class(es) there.<br/>
And finally instanciate your Bootstrap class somewhere in public `index.php` and run the application

### Bootstrap

On `index.php` for handling public requests.
```php
<?php
use Slim\App;
use MyProject\Bootstrap;

// application configuration
$conf = require('config.php');
// Instanciate and run
 (new Bootstrap(App::class,  $conf)
        ->addDefaultRoutes()
        ->addDefaultMiddlewares()
        ->run();
```

In unit tests. Let's say you want just to test your newly created routes (NewRoutes) without 
all the application middlewares controlling the input and output. 
```php
<?php
use Slim\App;
use MyProject\Bootstrap;
use MyProject\NewRoutes;

class MyRouteTest extends TestCase
{
    public function testRoute()
    {
        // test configuration
        $conf = [
            'settings' => [
                'price' => 100,
            ]
        ];
        $bootstrap = new Bootstrap(App::class, $conf);
        // add the routes class you wish to test
        $bootstrap->addRouteDefinitions(NewRoutes::class);
        // Generate a request with a right path and method for the route to test.
        // The request generation is out of the scope of this tutorial.
        $request = static::getRequest();
        // Process the request to get the response
        $response = $bootstrap->getApp()->process( $request, new Response());

        $content = (string)$response->getBody();
        // Validate the reponse    
        $this->assertContains(100, $content);
    }
...
}
```
Note, you are may call `$bootstrap->addDefaultRoutes()->addDefaultMiddlewares()` if you need to test a full
workflow.<br/>
Another important thing to remember. Never call a `$bootstrap->run()` method in tests. Instead, you must
use `$bootstrap->getApp()->process()` method. 

#### Adding default application middleware

Boostrap can add only application middleware, nor route neither group middleware. 

Sample: 
```php
     public function addAppMiddleware()
        {
            return $this->addMiddleware(
                ValidateResponse::class,
                SecurityHeaders::class,
                ValidateRequest::class,
                ApiInit::class
            );
        }
```
#### Adding default application routes

```php
 public function addAppRoutes()
     {
         return $this->addRouteDefinitions(
           HelpRoutes::class,
           MainRoutes::class
         );
     }
```
### Routes

Creating route(s). Sample. More on Slim routing can found at [Slim routing](https://www.slimframework.com/docs/v3/objects/router.html)

```php
namespace MyProject;

use Kod\BootstrapSlim\RouteDefintions;
use Slim\App;

/**
 * MockRoutes sets the route and content generation.
 */
class MyRoutes extends RouteDefintions
{
    /**
     * @param App $app
     */
    public function __invoke($app)
    {
        $app->get('/', function ($request, $response, $args) {
            $response->getBody()->write(' HELLO ');

            return $response;
        });
    }
}
```

### Middleware

Creating middleware. Sample. More on middleware can be found at [Slim middleware](https://www.slimframework.com/docs/v3/concepts/middleware.html)

```php
namespace MyProject;

use Kod\BootstrapSlim\Middleware;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * MiddlewareMock writes some content before and after content generation.
 */
class MykMiddleware extends Middleware
{
    /**
     * @param Request $request
     * @param Response $response
     * @param callable $next
     * @return Response
     */
    public function __invoke($request, $response, $next)
    {
        $response->getBody()->write('BEFORE');
        $response = $next($request, $response);
        $response->getBody()->write('AFTER');

        return $response;
    }
}
```