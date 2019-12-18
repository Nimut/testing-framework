<?php
namespace Nimut\Testbase\Tests\Functional\File;

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

use Nimut\TestingFramework\TestCase\FunctionalTestCase;

class FileStreamWrapperTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function importBackendUserDatabaseResource()
    {
        $this->importDataSet('ntf://Database/be_users.xml');

        $count = $this->getDatabaseConnection()->selectCount('*', 'be_users');

        $this->assertSame(1, $count);
    }

    /**
     * @test
     */
    public function importPagesDatabaseResource()
    {
        $this->importDataSet('ntf://Database/pages.xml');

        $count = $this->getDatabaseConnection()->selectCount('*', 'pages');

        $this->assertSame(7, $count);
    }

    /**
     * @test
     */
    public function importSysFileStorageDatabaseResource()
    {
        $this->importDataSet('ntf://Database/sys_file_storage.xml');

        $count = $this->getDatabaseConnection()->selectCount('*', 'sys_file_storage');

        $this->assertSame(1, $count);
    }

    /**
     * @test
     */
    public function importSysLanguageDatabaseResource()
    {
        $this->importDataSet('ntf://Database/sys_language.xml');

        $count = $this->getDatabaseConnection()->selectCount('*', 'sys_language');

        $this->assertSame(2, $count);
    }

    /**
     * @test
     */
    public function importTtContentDatabaseResource()
    {
        $this->importDataSet('ntf://Database/tt_content.xml');

        $count = $this->getDatabaseConnection()->selectCount('*', 'tt_content');

        $this->assertSame(1, $count);
    }
}
