<?php
namespace Nimut\TestingFramework\TestSystem;

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

class LegacyTestSystem extends AbstractTestSystem
{
    /**
     * Extensions that are always loaded
     *
     * @var array
     */
    protected $defaultActivatedCoreExtensions = array(
        'core',
        'backend',
        'frontend',
        'lang',
        'extbase',
        'install',
        'cms',
    );

    /**
     * Includes the Core Bootstrap class and calls its first few functions
     *
     * @return void
     */
    protected function includeAndStartCoreBootstrap()
    {
        $this->getClassLoaderFilepath();

        $this->bootstrap->baseSetup()
            ->initializeClassLoader()
            ->loadConfigurationAndInitialize(true)
            ->loadTypo3LoadedExtAndExtLocalconf(true)
            ->applyAdditionalConfigurationSettings();
    }
}
