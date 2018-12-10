<?php
/**
 * Copyright (c) 2018 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kod\BootstrapSlim\Sample;

use Kod\BootstrapSlim\Bootstrap;
use Slim\App;

/**
 * Class SampleBootstrap
 * @package Kod\BootstrapSlim\Sample
 */
class SampleBootstrap extends Bootstrap
{
    /**
     * Set default application middleware
     * @return static
     */
    public function addAppMiddleware()
    {
        return $this->addMiddleware(
            SampleMiddleware::class
        );
    }

    /**
     * Sample adding DI
     * @return static
     */
    public function addAppDependencies()
    {
        /**
         * @var \Pimple\Container $container
         */
        $container = $this->getContainer();
        $container['person'] = $container->protect(function ($name) {
            return 'Person name: ' . $name;
        });

        return $this;
    }

    /**
     * Set default routes
     * @return static
     */
    public function addAppRoutes()
    {
        return $this->addRouteDefinitions(
            SampleRoute::class,
            SampleRoute2::class
        );
    }
}
