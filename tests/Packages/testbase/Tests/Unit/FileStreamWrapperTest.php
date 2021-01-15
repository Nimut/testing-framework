<?php

namespace Nimut\Testbase\Tests\Unit;

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

use Nimut\TestingFramework\File\FileStreamWrapper;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;

/**
 * Test case for \Nimut\TestingFramework\File\FileStreamWrapper
 */
class FileStreamWrapperTest extends UnitTestCase
{
    /**
     * @test
     */
    public function pathsAreOverlaidAndFinalDirectoryStructureCanBeQueried()
    {
        $root = vfsStream::setup('root');
        $subfolder = vfsStream::newDirectory('fileadmin');
        $root->addChild($subfolder);
        // Load fixture files and folders from disk
        vfsStream::copyFromFileSystem(__DIR__ . '/Fixtures', $subfolder, 1024 * 1024);
        FileStreamWrapper::init(PATH_site);
        FileStreamWrapper::registerOverlayPath('fileadmin', 'vfs://root/fileadmin', false);

        // Use file functions as normal
        mkdir(PATH_site . 'fileadmin/test/');
        $file = PATH_site . 'fileadmin/test/Foo.bar';
        file_put_contents($file, 'Baz');
        $content = file_get_contents($file);
        $this->assertSame('Baz', $content);

        $expectedFileSystem = [
            'root' => [
                'fileadmin' => [
                    'ext_typoscript_setup.txt' => 'test.Core.TypoScript = 1',
                    'test' => ['Foo.bar' => 'Baz'],
                    'setup.typoscript' => 'test.TYPO3Forever.TypoScript = 1
',
                    'recursive_includes_setup.typoscript' => '@import \'EXT:core/Tests/Unit/TypoScript/Fixtures/setup.typoscript\'
',
                    'badfilename.php' => 'good.bad = ugly
',
                ],
            ],
        ];
        $this->assertEquals($expectedFileSystem, vfsStream::inspect(new vfsStreamStructureVisitor())->getStructure());
        FileStreamWrapper::destroy();
    }

    /**
     * @test
     */
    public function windowsPathsCanBeProcessed()
    {
        $cRoot = 'C:\\Windows\\Root\\Path\\';
        vfsStream::setup('root');
        FileStreamWrapper::init($cRoot);
        FileStreamWrapper::registerOverlayPath('fileadmin', 'vfs://root/fileadmin');

        touch($cRoot . 'fileadmin\\someFile.txt');
        $expectedFileStructure = [
            'root' => [
                'fileadmin' => ['someFile.txt' => null],
            ],
        ];

        $this->assertEquals($expectedFileStructure, vfsStream::inspect(new vfsStreamStructureVisitor())->getStructure());
        FileStreamWrapper::destroy();
    }
}
