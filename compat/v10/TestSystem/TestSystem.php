<?php

namespace Nimut\TestingFramework\v10\TestSystem;

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

use Nimut\TestingFramework\TestSystem\AbstractTestSystem;
use TYPO3\CMS\Core\Core\Bootstrap;

class TestSystem extends AbstractTestSystem
{
    /**
     * Loads TCA and ext_tables.php files from extensions
     *
     * @return void
     */
    protected function loadExtensionConfiguration()
    {
        Bootstrap::initializeBackendRouter();
        Bootstrap::loadExtTables(true);
    }
}
