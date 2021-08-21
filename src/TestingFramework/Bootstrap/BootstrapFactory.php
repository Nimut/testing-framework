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

final class BootstrapFactory
{
    /**
     * Analyses the system and returns proper bootstrap instance
     *
     * @return AbstractBootstrap
     */
    public static function createBootstrapInstance()
    {
        return new Bootstrap();
    }
}
