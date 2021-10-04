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

use Nimut\TestingFramework\Exception\Exception;

/**
 * Very basic artifact builder, which does not take ordering by dependency into account at all
 */
class PackageArtifactBuilder
{
    /**
     * @var string
     */
    protected $instancePath;

    public function __construct($instancePath)
    {
        $this->instancePath = $instancePath;
    }

    public function writePackageArtifact($packageStatesConfiguration)
    {
        $result = file_put_contents(
            $this->instancePath . 'typo3conf/PackageStates.php',
            '<?php' . chr(10) . 'return ' . var_export($packageStatesConfiguration, true) . ';' . chr(10) . '?>'
        );

        if (!$result) {
            throw new Exception('Can not write PackageStates', 1381612729);
        }
    }
}
