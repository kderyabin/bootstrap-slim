<?php
/**
 * Copyright (c) 2019 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Kod\BootstrapSlim;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

/**
 * Class MiddlewareHandler
 * @package Kod\BootstrapSlim
 */
abstract class MiddlewareHandler implements MiddlewareInterface
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
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     */
    abstract public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface;
}
