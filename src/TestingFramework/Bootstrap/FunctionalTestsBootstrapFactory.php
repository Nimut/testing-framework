<?php
namespace Nimut\TestingFramework\Bootstrap;

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

use Nimut\TestingFramework\Bootstrap\Functional\AbstractBootstrap;
use Nimut\TestingFramework\Bootstrap\Functional\Bootstrap;
use Nimut\TestingFramework\Bootstrap\Functional\OldBootstrap;

final class FunctionalTestsBootstrapFactory
{
    /**
     * Analyses the system and returns proper bootstrap instance
     *
     * @return AbstractBootstrap
     */
    public static function getBootstrapInstance()
    {
        if (interface_exists('TYPO3Fluid\\Fluid\\View\\ViewInterface')) {
            return new Bootstrap();
        }

        return new OldBootstrap();
    }
}
