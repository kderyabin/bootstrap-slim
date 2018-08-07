<?php
/**
 * Copyright (C)  2018 Orange
 *
 * This software is confidential and proprietary information of Orange.
 * You shall not disclose such Confidential Information and shall use it only
 * in accordance with the terms of the agreement you entered into.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 */
/**
 * Created on 2018-08-07
 */

namespace MDCS\Slim\Tests\Mocks;

use MDCS\Slim\Bootstrap;
use Psr\Container\ContainerInterface;

/**
 * Class MockBootstrap
 * @package MDCS\Slim\Tests\Mocks
 */
class MockBootstrap extends Bootstrap
{
    /**
     * Set default application middleware
     * @return static
     */
    public function addDefaultMiddlewares()
    {
        return $this->addMiddleware(
            MockMiddleware::class
        );
    }

    /**
     * Set default routes
     * @return static
     */
    public function addDefaultRoutes()
    {
        return $this->addRoutes(
            MockRoutes::class
        );
    }
}
