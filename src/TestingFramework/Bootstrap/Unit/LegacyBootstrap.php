<?php
namespace Nimut\TestingFramework\Bootstrap\Unit;

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

use TYPO3\CMS\Core\Core\Bootstrap as CoreBootstrap;

/**
 * Unit Test Bootstrap for TYPO3 ^6.2
 */
class LegacyBootstrap extends AbstractBootstrap
{
    /**
     * Includes the Core Bootstrap class and calls its first few functions
     *
     * @return void
     */
    protected function includeAndStartCoreBootstrap()
    {
        $this->getClassLoaderFilepath();

        $bootstrap = CoreBootstrap::getInstance();
        $bootstrap->baseSetup()
            ->initializeClassLoader();
    }

    /**
     * Initializes core cache handling
     *
     * @return void
     */
    protected function initializeCachingHandling()
    {
        $bootstrap = CoreBootstrap::getInstance();
        $bootstrap->disableCoreAndClassesCache()
            ->initializeCachingFramework()
            ->initializeClassLoaderCaches();
    }
}
