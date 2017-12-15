<?php
namespace Nimut\TestingFramework\Bootstrap;

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

use Composer\Autoload\ClassLoader;

final class BootstrapFactory
{
    /**
     * Analyses the system and returns proper bootstrap instance
     *
     * @return AbstractBootstrap
     */
    public static function createBootstrapInstance()
    {
        if (class_exists('TYPO3\\CMS\\Install\\Service\\ExtensionConfigurationService')) {
            return new Bootstrap();
        }

        if (interface_exists('TYPO3Fluid\\Fluid\\View\\ViewInterface')) {
            self::initializeCompatibilityLayer('v87');
        } else {
            self::initializeCompatibilityLayer('v76');
        }

        return new Bootstrap();
    }

    private static function initializeCompatibilityLayer($version)
    {
        $compatibilityClassesPath = __DIR__ . '/../../../compat/' . $version . '/';
        $compatibilityNamespace = 'Nimut\\TestingFramework\\' . $version . '\\';

        $classLoader = new ClassLoader();
        $classLoader->addPsr4($compatibilityNamespace, $compatibilityClassesPath);

        spl_autoload_register(function ($className) use ($classLoader, $compatibilityNamespace) {
            if (strpos($className, 'Nimut\\TestingFramework\\') === false) {
                return;
            }

            $compatibilityClassName = str_replace('Nimut\\TestingFramework\\', $compatibilityNamespace, $className);
            if ($file = $classLoader->findFile($compatibilityClassName)) {
                require $file;
                class_alias($compatibilityClassName, $className);
            }
        }, true, true);
    }
}
