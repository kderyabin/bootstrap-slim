# Bootstrap SLIM  3

This repository holds a library for bootstrapping SLIM 3 application. 

Have a look on the [Slim 3 skeleton](https://github.com/kderyabin/slim-skeleton) built on top of 
this package for ready to use implementation.

See also [sample classes](./samples)

## Purpose

- Centralize the application's initialisation
- Reuse existing configuration in unit tests
- Create on the fly a new customized configuration, middleware or routes for unit tests
- Reduce usage of `require`/`include` statements.

## Introduction

This package allows you to play with different parts of your application assembling them all together according to your needs.

For instance, let's say you want just to test your newly created route (NewRoute) without all the application middleware 
stack controlling the input and output and all settings. You can bootstrap your app like this:
```php
$config = [ 
    'settings' => [
        'test_param' => 'test_value',
    ] 
];
$bootstrap = (new Bootstrap(App::class, $config))
    ->addRouteDefinitions(
        NewRoute::class
    );
```
Now the same route but with application's middleware :
```php
$bootstrap = (new Bootstrap(App::class, $config))
    ->addAppMiddleware()
    ->addRouteDefinitions(
        NewRoute::class
    );
```
And if you want a complete workflow:

```php
$config = require '/path/to/app/config.php';
$bootstrap = (new Bootstrap(App::class, $config))
    ->addAppDependencies()
    ->addAppRoutes()
    ->addAppMiddleware()
```
You can go even further by declaring routes and/or middleware on the fly which is quite useful for 
test driven development.
```php
$bootstrap = (new Bootstrap(App::class, $config))
    ->addMiddleware(
          function ($request, $response, $next) {
              $response->getBody()->write('*START*');
              $response = $next($request, $response);
              $response->getBody()->write('*END*');
    
              return $response;
          }
    )
    ->addRouteDefinitions(
        function ($app) {
            $app->get('/hello/{name}', function ($request, $response, $args) {
                return $response;
            })->setName('hello');
        }
    );
```
 


## What's in the library

| File | Description  |
|:---|:--- |
|`Middleware.php` | Abstract class for middleware declaration.|
|`RouteDefintions.php` | Abstract class for routs(s) declaration| 
|`Bootstrap.php` | Main class where application routes and middleware stack are declared| 


## Installation

Add this package to `require` section of your `composer.json`
```
composer require kod/bootstrap-slim
```
## Usage

### Bootstrap

Create a Bootstrap class by extending `Kod\BootstrapSlim\Bootstrap` class and instantiate it in public `index.php` to run the application like this:
```php
<?php
use Slim\App;
use MyProject\Bootstrap;

// application configuration
$conf = require('config.php');
// Instantiate and run
 (new Bootstrap(App::class,  $conf))
        ->addAppDependencies()
        ->addAppRoutes()
        ->addAppMiddleware()
        ->run();
 
```
For now it does not do that much and is not able to process requests. You need to:
* Create routes and declare them in `Bootstrap::addAppRoutes()` method
* Create middleware classes and declare them in `Bootstrap::addAppMiddleware()` method
* [optionnal] Declare your dependencies in `Bootstrap::addAppDependencies()` method if you want to use some 
advanced features offered by Slim Container like using `factory()` or `protect()` methods etc...

### Middleware

Create a middleware class  by extending  `Kod\BootstrapSlim\Middleware` abstract class 
and implement your business logic in `__invoke($request, $response, $next)` method. 
Protected class property `$ci` contains a reference to the application dependency container. 
You can fetch services from the container like this: 


```php
class MyMiddleware extends Middleware
{
    /**
     * @param Request $request
     * @param Response $response
     * @param callable $next
     * @return Response
     */
    public function __invoke($request, $response, $next)
    {
        // get logger service
        $logger = $this->ci->get('logger');
        $logger->info('Middleware start');
        $response->getBody()->write('>>> before');
        $response = $next($request, $response);
        /**
         * @var Response $response
         */
        $response->getBody()->write('<<< after');

        return $response;
    }
}
```

Next step is to declare it in `Bootstrap:addAppMiddleware` method so it 
can be injected into the slim application when called from public index.php page.
Use following syntax :
```php   
public function addAppMiddleware()
{
    return $this->addMiddleware(
        ValidateResponse::class,
        MyRoute::class,
        ValidateRequest::class
    );
}

```

### Routes

Create a class containing your route declaration by extending `Kod\BootstrapSlim\RouteDefintions` abstract class.
 Define your route in `__invoke($app)` method like in example below. This method receives an instance of 
 the Slim\App class. You may declare all application's routes in one class or have a distinct class per route. 
 To find more on route's declaration see [Slim Router docs](https://www.slimframework.com/docs/v3/objects/router.html).
```php
class MyRoute extends RouteDefinitions
{
    /**
     * @param App $app  Slim App class instance
     */
    public function __invoke($app)
    {
        // defined as a closure  with a middleware
        $app->get('/', function ($request, $response) {
            $response->getBody()->write('Some content to display');
            
            return $response;
        })->add(new MyRouteMiddleware($app->getContainer()));
        
        // define as a class
        $app->get( '/mvc', MyController::class . ':index');
        
        // define a route
        $app->get( '/test', MyController::class . ':index')
        $app->get( '/test', MyController::class . ':index')
    }
}
```
Now add this route to `Bootstrap::addAppRoutes` method. 

```php
public function addAppRoutes()
{
    return $this->addRouteDefinitions(
        MyRoute::class,
        SomeOtherRoute::class
    );
}
```
### Dependency injection

This is optional. Normally your dependencies are declared as an array in configuration file 
and injected into the Container by `Slim\App`. But if you want to use some advanced features 
offered by the Slim Container like using `factory()` or `protect()` methods you can do it 
in your `Bootstrap::addAppDependencie()` method like this:

```php
public function addAppDependencies()
{
   $container = $this->getContainer();
   // for pimple based container
   $container['myclass'] = $container->factory(function ($c) {
       return new MyClass($c['myclass_config']);
   });
   return $this;
}
```

That's it. 

## Unit Tests

Some useful tips for unit testing.

### Forging requests

For `GET` requests and, more generally, for any request without the body and/or uploaded files you 
need to set up the environment only prior to bootstrapping. 
The request wil be generated by Slim itself. 
```php
$conf = [
    'environment' => Environment::mock([
        'REQUEST_METHOD' => 'GET',
        'REQUEST_URI' => '/',
    ]),
];
// initialize the app
$bootstrap = new MyBootstrap(App::class, $conf);
```
For requests requiring body content like `POST` you need to generate the request along with 
the environment.
```php
$env = Environment::mock([
   'REQUEST_METHOD' => 'POST',
   'REQUEST_URI' => '/',
];
$request = Request::createFromEnvironment($env);
// set POST data
$request = $request->withParsedBody([
    'field' => 'value'
]);
$conf = [
    'environment' => $env,
    'request' => $request,
];
// initialize the app
$bootstrap = new MyBootstrap(App::class, $conf);
```

To execute the request use `Bootstrap::run()` method with `true` parameter.
```php
$response = $bootstrap->run(true);
```

Another approach for processing a request is to use `Slim\App::process()` method (not fully tested). 
In this case you do not need to inject environment and request into the container. 
```php
$env = Environment::mock([
   'REQUEST_METHOD' => 'POST',
   'REQUEST_URI' => '/',
];
$request = Request::createFromEnvironment($env);
// set POST data
$request = $request->withParsedBody([
    'field' => 'value'
]);

// initialize the app
$bootstrap = new MyBootstrap(App::class, $conf);
...
$response = $bootstrap->getApp()->process($request, new Response());
```


