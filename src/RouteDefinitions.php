<?php
/**
 * Copyright (c) 2018 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Kod\BootstrapSlim;

/**
 * RouteDefinitions base class.
 */
abstract class RouteDefinitions
{
    /**
     * @param object $app Slim App instance.
     */
    abstract public function __invoke($app);
}
