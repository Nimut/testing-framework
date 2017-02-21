<?php
namespace Nimut\TestingFramework\Rendering;

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

use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;

/**
 * Fixture for RenderingContext
 */
class RenderingContextFixture extends RenderingContext
{
    /**
     * Prevent any superfluous object initialization
     */
    public function __construct()
    {
    }
}
