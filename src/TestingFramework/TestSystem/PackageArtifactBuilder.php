<?php

namespace Nimut\TestingFramework\TestSystem;

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
use TYPO3\CMS\Core\Package\Cache\PackageCacheEntry;
use TYPO3\CMS\Core\Package\Package;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Service\DependencyOrderingService;

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
        $packageManager = new PackageManager(new DependencyOrderingService(), '', '');
        $composerNameToPackageKeyMap = [];
        $packageAliasMap = [];
        $packages = [];

        foreach ($packageStatesConfiguration['packages'] as $extensionKey => $stateConfig) {
            $packagePath = $this->instancePath . $stateConfig['packagePath'];
            $package = new Package($packageManager, $extensionKey, $packagePath);
            $composerNameToPackageKeyMap[$package->getValueFromComposerManifest('name')] = $extensionKey;
            $packages[$extensionKey] = $package;
            foreach ($package->getPackageReplacementKeys() as $packageToReplace => $versionConstraint) {
                $packageAliasMap[$packageToReplace] = $extensionKey;
            }
        }

        $cacheEntry = PackageCacheEntry::fromPackageData(
            $packageStatesConfiguration,
            $packageAliasMap,
            $composerNameToPackageKeyMap,
            $packages
        )->withIdentifier(md5('typo3-testing-' . $this->instancePath));

        $result = file_put_contents(
            $this->instancePath . '/typo3temp/var/build/PackageArtifact.php',
            '<?php' . PHP_EOL . 'return ' . PHP_EOL . $cacheEntry->serialize() . ';'
        );

        if (!$result) {
            throw new Exception('Can not write PackageArtifact', 1630268883);
        }
    }
}
