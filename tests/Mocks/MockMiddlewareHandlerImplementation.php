<?php
/**
 * Copyright (c) 2018 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Kod\BootstrapSlim\Tests\Mocks;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

/**
 * Write some content before and after content generation.
 */
class MockMiddlewareHandlerImplementation implements MiddlewareInterface
{
    public static $contentBefore = 'HANDLER-BEFORE';
    public static $contentAfter = 'HANDLER-AFTER';

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
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $existingContent = (string) $response->getBody();

        $response = new Response();
        $response->getBody()->write(
            static::$contentBefore .
            $existingContent .
            static::$contentAfter
        );

        return $response;
    }
}
