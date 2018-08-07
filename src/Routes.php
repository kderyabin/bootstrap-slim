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

use Psr\Http\Message\ResponseInterface;

/**
 * Routes base class.
 */
abstract class Routes
{
    /**
     * @param object $app Slim App instance.
     */
    abstract public function __invoke($app);
}
