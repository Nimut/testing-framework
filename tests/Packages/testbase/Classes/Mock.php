<?php
namespace Nimut\Testbase;

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

class Mock
{
    /**
     * @var string
     */
    protected $aProtectedProperty;

    /**
     * @param string $property
     */
    public function __construct($property = null)
    {
        $this->aProtectedProperty = $property;
    }

    /**
     * @return string
     */
    public function getAProtectedProperty()
    {
        return $this->aProtectedProperty;
    }
}
