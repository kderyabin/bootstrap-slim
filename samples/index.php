<?php
/**
 * Copyright (c) 2018 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Slim\App;
use Kod\BootstrapSlim\Sample\SampleBootstrap as Bootstrap;

// application configuration
$conf = require('config.php');
// Instantiate and run
(new Bootstrap(App::class, $conf))
    ->addAppDependencies()
    ->addAppRoutes()
    ->addAppMiddleware()
    ->run();
