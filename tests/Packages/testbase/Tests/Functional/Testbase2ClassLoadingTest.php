<?php

use Nimut\Testbase2\BaseClass;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;

class Testbase2ClassLoadingTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/testbase/Tests/Packages/testbase2',
    ];

    /**
     * @test
     */
    public function baseClassIsLoadable()
    {
        $this->assertTrue(class_exists(BaseClass::class));
    }

    /**
     * @test
     */
    public function extensionConfigurationIsAvailable()
    {
        $this->assertSame('bar', $GLOBALS['TYPO_CONF_VARS']['EXTCONF']['testbase2']['foo']);
    }
}
