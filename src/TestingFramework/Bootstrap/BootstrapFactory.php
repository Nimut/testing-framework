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
        spl_autoload_register(
            function ($className) use ($version) {
                if (strpos($className, 'Nimut\\TestingFramework\\') !== 0) {
                    return;
                }

                $compatibilityClassName = str_replace('Nimut\\TestingFramework\\', 'Nimut\\TestingFramework\\' . $version . '\\', $className);

                $classLoaderFilepath = __DIR__ . '/../../../../../autoload.php';
                if (file_exists($classLoaderFilepath)) {
                    $classLoader = require $classLoaderFilepath;
                } elseif (file_exists(__DIR__ . '/../../../.Build/vendor/autoload.php')) {
                    $classLoader = require __DIR__ . '/../../../.Build/vendor/autoload.php';
                } else {
                    throw  new \RuntimeException('ClassLoader can\'t be loaded.', 1513379551);
                }
                if ($file = $classLoader->findFile($compatibilityClassName)) {
                    require $file;
                    class_alias($compatibilityClassName, $className);
                }
            },
            true,
            true
        );
    }
}
