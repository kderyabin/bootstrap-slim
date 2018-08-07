<?php
/**
 * Copyright (C)  2018 Orange
 *
 * This software is confidential and proprietary information of Orange.
 * You shall not disclose such Confidential Information and shall use it only
 * in accordance with the terms of the agreement you entered into.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 */

namespace MDCS\Slim;

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
    abstract public function __invoke($request, $response, $next);
}
