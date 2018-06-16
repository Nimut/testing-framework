<?php
namespace Nimut\Testbase\Controller;

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

class DeprecationController
{
    /**
     * @return bool
     */
    public function someDeprecatedMethod()
    {
        trigger_error(
            'This is some deprecated method that will never be removed.',
            E_USER_WARNING
        );

        return true;
    }
}
