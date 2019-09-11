<?php
/**
 * Copyright (c) 2018 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Kod\BootstrapSlim;


use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Environment;
use Slim\Psr7\Factory\ServerRequestFactory;

/**
 * Helper for unit testing
 * @package Kod\BootstrapSlim
 */
class Utils
{
    /**
     * @param array $env
     * @return ServerRequestInterface
     */
    public static function getRequest(array $env): ServerRequestInterface
    {
        static::setEnv($env);
        return ServerRequestFactory::createFromGlobals();
    }

    /**
     * @param array $data
     * @return void
     */
    public static function setEnv(array $data): void
    {
        $_SERVER = Environment::mock($data);
    }
}
