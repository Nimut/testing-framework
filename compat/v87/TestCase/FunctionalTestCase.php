<?php
namespace Nimut\TestingFramework\v87\TestCase;

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

use Nimut\TestingFramework\TestCase\AbstractFunctionalTestCase;

abstract class FunctionalTestCase extends AbstractFunctionalTestCase
{
    /**
     * @param int $pageId
     * @param array $typoScriptFiles
     * @param array $sites
     */
    protected function setUpFrontendRootPage($pageId, array $typoScriptFiles = [], array $sites = [])
    {
        $pageId = (int)$pageId;

        $this->setUpPageRecord($pageId);
        $this->setUpTemplateRecord($pageId, $typoScriptFiles);
    }
}
