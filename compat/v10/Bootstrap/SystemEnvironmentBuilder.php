<?php

declare(strict_types=1);

namespace Nimut\TestingFramework\v10\Bootstrap;

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

use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder as CoreSystemEnvironmentBuilder;

/**
 * Class that replaces the core's SystemEnvironmentBuilder.
 *
 * It is used to adapt the default bootstrap to the requirements of the
 * Testing Framework, basically enforcing the created TYPO3 instances to not
 * be in Composer mode, so that the PackageManager reads PackageStates.php even in TYPO3 v11
 *
 * The TYPO3 testing instances that are set up by the testing framework are created by linking
 * folders and entry scripts. This makes these instances non Composer instances by definition.
 * However, since at the same time the autoload.php from the root installation is pulled in
 * and this sets a PHP constant, we need to make sure to override this environment property
 * for each created testing instance. This is done by overriding the Core SystemEnvironmentBuilder
 * with a custom one, which sets Composer Mode to false.
 *
 * All that can be removed, once the testing framework creates testing instances using Composer
 *
 * @internal
 */
class SystemEnvironmentBuilder extends CoreSystemEnvironmentBuilder
{
}
