<?php
/**
 * Copyright (c) 2018 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Kod\BootstrapSlim\Tests\Mocks;

use Kod\BootstrapSlim\Bootstrap;
use Psr\Container\ContainerInterface;

/**
 * Class MockBootstrap
 * @package Kod\BootstrapSlim\Tests\Mocks
 */
class MockBootstrap extends Bootstrap
{
    /**
     * Set default application middleware
     * @return static
     */
    public function addAppMiddleware()
    {
        return $this->addMiddleware(
            MockMiddleware::class
        );
    }

    /**
     * Set default routes
     * @return static
     */
    public function addAppRoutes()
    {
        return $this->addRouteDefinitions(
            MockRouteDefinitions::class
        );
    }
}
