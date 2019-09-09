<?php
namespace Nimut\TestingFramework\v87\Bootstrap;

/*
 * This file is part of the NIMUT testing-framework project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read
 * LICENSE file that was distributed with this source code.
 */

use Nimut\TestingFramework\Bootstrap\AbstractBootstrap;

/**
 * Unit Test Bootstrap for TYPO3 9.5
 */
class Bootstrap extends AbstractBootstrap
{
    /**
     * @var string
     */
    protected $coreCacheName = 'cache_core';
}
