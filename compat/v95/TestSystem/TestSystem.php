<?php
namespace Nimut\TestingFramework\v95\TestSystem;

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

use Nimut\TestingFramework\Exception\Exception;
use Nimut\TestingFramework\TestSystem\AbstractTestSystem;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;

class TestSystem extends AbstractTestSystem
{
    /**
     * @param string $identifier Name of test case class
     * @param Bootstrap $bootstrap
     */
    public function __construct($identifier, Bootstrap $bootstrap = null)
    {
        parent::__construct($identifier, $bootstrap);
        $this->bootstrap = $this->bootstrap === null ? Bootstrap::getInstance() : $this->bootstrap;
    }

    /**
     * Includes the Core Bootstrap class and calls its first few functions
     *
     * @return void
     */
    protected function includeAndStartCoreBootstrap()
    {
        $classLoaderFilepath = $this->getClassLoaderFilepath();
        $classLoader = require $classLoaderFilepath;

        SystemEnvironmentBuilder::run(0, SystemEnvironmentBuilder::REQUESTTYPE_BE | SystemEnvironmentBuilder::REQUESTTYPE_CLI);
        Bootstrap::init($classLoader);
        ob_end_clean();
    }

}
