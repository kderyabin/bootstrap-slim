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
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

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
     * @param  ServerRequestInterface $request
     * @param  RequestHandlerInterface $handler
     *
     * @return ResponseInterface

     */
    abstract public function __invoke(
        ServerRequestInterface $request,
        RequestHandlerInterface  $handler
    ): ResponseInterface;
}
