<?php
/**
 * Copyright (c) 2018 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kod\BootstrapSlim;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Middleware base class
 */
abstract class Middleware
{
    /**
     * @var ContainerInterface
     */
    protected $ci;

    public function __construct(ContainerInterface $container)
    {
        $this->ci = $container;
    }


    /**
     * Middleware main method.
     *
     * @param  ServerRequestInterface $request PSR7 request
     * @param  ResponseInterface $response PSR7 response
     * @param  callable $next Next middleware
     *
     * @return ResponseInterface
     */
    abstract public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        $next
    );
}
