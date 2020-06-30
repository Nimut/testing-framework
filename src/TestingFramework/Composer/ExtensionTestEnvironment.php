<?php
namespace Nimut\TestingFramework\Composer;

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

use Composer\Script\Event;
use Composer\Util\Filesystem;
use TYPO3\CMS\Composer\Plugin\Config;
use TYPO3\CMS\Composer\Plugin\Util\ExtensionKeyResolver;

/**
 * If a TYPO3 extension should be tested, the extension needs to be embedded in
 * a TYPO3 instance. The composer.json file of the extension creates a
 * TYPO3 project around the extension code in a build folder like "./.Build".
 * The to-test extension then needs to reside in ./.Build/public/typo3conf/ext.
 * This composer script takes care of this operation and links the current
 * root directory as "./<root-dir>/typo3conf/ext/<extension-key>".
 *
 * This class is added as composer "script" in TYPO3 extensions:
 *
 *   "scripts": {
 *     "post-autoload-dump": [
 *       "@prepare-extension-test-structure"
 *     ],
 *     "prepare-extension-test-structure": [
 *       "Nimut\\TestingFramework\\Composer\\ExtensionTestEnvironment::prepare"
 *     ]
 *   },
 *
 * It additionally needs the "extension key" (that will become the directory name in
 * typo3conf/ext) and the name of the target directory in the extra section. Example for
 * a extension "my_extension":
 *
 *   "extra": {
 *     "typo3/cms": {
 *       "web-dir": ".Build/public",
 *       "extension-key": "my_extension"
 *     }
 *   }
 */
class ExtensionTestEnvironment
{
    /**
     * Link base directory as ./<root-dir>/typo3conf/ext/<extension-key>
     */
    public static function prepare(Event $event)
    {
        $composer = $event->getComposer();
        $io = $event->getIO();

        $config = Config::load($composer, $io);
        $rootPackage = $composer->getPackage();

        $rootDir = $config->get('root-dir');
        $extensionKey = ExtensionKeyResolver::resolve($rootPackage);

        $typo3ExtDir = $rootDir . '/typo3conf/ext';
        $extDir = $typo3ExtDir . '/' . $extensionKey;
        $fileSystem = new Filesystem();
        $fileSystem->ensureDirectoryExists($typo3ExtDir);

        if (!file_exists($extDir)) {
            symlink($config->getBaseDir(), $extDir);
        }
    }
}
