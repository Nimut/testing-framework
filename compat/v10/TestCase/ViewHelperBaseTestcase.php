<?php

namespace Nimut\TestingFramework\v10\TestCase;

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

use Nimut\TestingFramework\TestCase\AbstractViewHelperBaseTestcase;

/**
 * Base test class for testing view helpers
 */
abstract class ViewHelperBaseTestcase extends AbstractViewHelperBaseTestcase
{
    protected function getUriBuilder()
    {
        $uriBuilder = parent::getUriBuilder();
        $uriBuilder->expects($this->any())->method('setUseCacheHash')->will($this->returnValue($this->uriBuilder));

        return $uriBuilder;
    }

    protected function getRequest()
    {
        return $this->prophesize(\TYPO3\CMS\Extbase\Mvc\Web\Request::class);
    }
}
