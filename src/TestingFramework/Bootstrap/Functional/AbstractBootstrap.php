<?php
namespace Nimut\TestingFramework\Bootstrap\Functional;

/*
 * This file is part of the NIMUT testing-framework project.
 *
 * It was taken from the TYPO3 CMS project (www.typo3.org).
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read
 * LICENSE file that was distributed with this source code.
 */

use Nimut\TestingFramework\Bootstrap\AbstractTestsBootstrap;

abstract class AbstractBootstrap extends AbstractTestsBootstrap
{
    /**
     * Bootstraps the system for functional tests
     *
     * @return void
     */
    public function bootstrapSystem()
    {
        $this->enableDisplayErrors();
        $this->defineOriginalRootPath();
        $this->createNecessaryDirectoriesInDocumentRoot();
    }

    /**
     * Creates the following directories in the TYPO3 core:
     * - typo3temp/var/tests
     * - typo3temp/var/transient
     *
     * @return void
     */
    protected function createNecessaryDirectoriesInDocumentRoot()
    {
        $this->createDirectory(ORIGINAL_ROOT . 'typo3temp/var/tests');
        $this->createDirectory(ORIGINAL_ROOT . 'typo3temp/var/transient');
    }

    /**
     * Defines the constant ORIGINAL_ROOT for the path to the original TYPO3 document root
     *
     * @return void
     */
    protected function defineOriginalRootPath()
    {
        if (!defined('ORIGINAL_ROOT')) {
            /** @var string */
            define('ORIGINAL_ROOT', $this->getWebRoot());
        }

        if (!file_exists(ORIGINAL_ROOT . 'typo3/cli_dispatch.phpsh')) {
            $this->exitWithMessage('Unable to determine path to entry script.'
                . ' Please check your path or set an environment variable \'TYPO3_PATH_WEB\' to your root path.'
            );
        }
    }
}
