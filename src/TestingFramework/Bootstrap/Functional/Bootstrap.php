<?php
namespace Nimut\TestingFramework\Bootstrap\Functional;

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

/**
 * Functional Test Bootstrap for TYPO3 >= 8.0
 */
class Bootstrap extends AbstractBootstrap
{
    /**
     * Defines the constant ORIGINAL_ROOT for the path to the original TYPO3 document root
     *
     * @return void
     */
    protected function defineOriginalRootPath()
    {
        parent::defineOriginalRootPath();
        parent::defineBaseConstants();
    }
}
