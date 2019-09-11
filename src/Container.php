<?php
/**
 * Copyright (c) 2019 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Kod\BootstrapSlim;

use Kod\BootstrapSlim\Exceptions\ContainerException;
use Kod\BootstrapSlim\Exceptions\NotFoundException;
use Pimple\Exception\UnknownIdentifierException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Throwable;

class Container extends \Pimple\Container implements ContainerInterface
{

    /**
     * @param string $id
     *
     * @return mixed
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     *
     * @throws NotFoundExceptionInterface  No entry was found for identifier.
     */
    public function get($id)
    {

        try {
            return $this->offsetGet($id);
        } catch (UnknownIdentifierException $exception) {
            throw new NotFoundException($exception->getMessage(), $exception->getCode());
        } catch (Throwable $throwable) {
            throw new ContainerException($throwable->getMessage(), $throwable->getCode());
        }
    }

    public function has($id)
    {
        return $this->offsetExists($id);
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->has($name);
    }
}
