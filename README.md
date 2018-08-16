# Testing Framework for TYPO3 CMS Extensions

[![Latest Stable Version](https://img.shields.io/packagist/v/nimut/testing-framework.svg)](https://packagist.org/packages/nimut/testing-framework)
[![Build Status](https://img.shields.io/travis/Nimut/testing-framework/master.svg)](https://travis-ci.org/Nimut/testing-framework)
[![StyleCI](https://styleci.io/repos/81999184/shield?branch=master)](https://styleci.io/repos/81999184)

The aim of the testing framework is to provide a good way to write and run unit and functional tests for multiple versions
of the TYPO3 CMS. Currently **TYPO3 CMS 7.6 up to master (9.0)** are tested and supported.

## Installation

Use [Composer](https://getcomposer.org/) to install the testing framework.

```bash
$ composer require --dev nimut/testing-framework
```

Composer will add the package as a dev requirement to your composer.json and install PHPUnit and vfsStream as its
dependencies.

## Usage

### Unit Tests

Inherit your test class from `\Nimut\TestingFramework\TestCase\UnitTestCase`.

To execute the unit tests of your extension run

```bash
$ vendor/bin/phpunit -c vendor/nimut/testing-framework/res/Configuration/UnitTests.xml \
    typo3conf/ext/example_extension/Tests/Unit
```

#### ViewHelper

For an easy way to test your Fluid ViewHelper you can inherit the test class from `\Nimut\TestingFramework\TestCase\ViewHelperBaseTestcase`.

You should setup your subject class in your setUp() method of the test class: 

```php
/**
 * @var \PHPUnit_Framework_MockObject_MockObject
 */
protected $viewHelper;

protected function setUp()
{
    parent::setUp();
    $this->viewHelper = $this->getMock(RenderChildrenViewHelper::class, ['renderChildren']);
    $this->injectDependenciesIntoViewHelper($this->viewHelper);
    $this->viewHelper->initializeArguments();
}
```

### Functional Tests

Inherit your test class from `\Nimut\TestingFramework\TestCase\FunctionalTestCase`.

To execute the functional tests of your extension run

```bash
$ vendor/bin/phpunit -c vendor/nimut/testing-framework/res/Configuration/FunctionalTests.xml \
    typo3conf/ext/example_extension/Tests/Functional
```

### Database abstraction

To be able to test against TYPO3 CMS 8 and later, nimut/testing-framework provides an own database abstraction layer.

In your FunctionalTestCase call `$this->getDatabaseConnection()` to get an instance of 
`\Nimut\TestingFramework\Database\DatabaseInterface`.

Following database functions are built in the nimut/testing-framework database interface:

- select
- selectSingleRow
- selectCount
- insertArray
- lastInsertId
- updateArray
- delete
- getDatabaseInstance

If you need own database requests you can get the proper database instance of the current TYPO3 version by using
`$this->getDatabaseConnection()->getDatabaseInstance()`. You have to check weather this instance is a
`\TYPO3\CMS\Core\Database\Query\QueryBuilder` (TYPO3 CMS 8 and above) or an instance of 
`\TYPO3\CMS\Core\Database\DatabaseConnection` (TYPO3 CMS 7).

#### Database fixtures

The nimut/testing-framework ships database fixtures for several TYPO3 CMS core database tables:

- pages
- pages_language_overlay
- sys_file_storage
- sys_language
- tt_content

To use the database fixtures you can trigger an import in your test file

```php
$this->importDataSet('ntf://Database/pages.xml');
```

### Frontend requests

The nimut/testing-framework ships an own TypoScript file for supporting frontend requests out of the box.

```php
// First import some page records
$this->importDataSet('ntf://Database/pages.xml');

// Import tt_content record that should be shown on your home page
$this->importDataSet('ntf://Database/tt_content.xml');

// Setup the page with uid 1 and include the TypoScript as sys_template record
$this->setUpFrontendRootPage(1, array('ntf://TypoScript/JsonRenderer.ts'));

// Fetch the frontend response
$response = $this->getFrontendResponse(1);

// Assert no error has occured
$this->assertSame('success', $response->getStatus());

// Get the first section from the response
$sections = $response->getResponseSections();
$defaultSection = array_shift($sections);

// Get the section structure
$structure = $defaultSection->getStructure();

// Make assertions for the structure
$this->assertTrue(is_array($structure['pages:1']['__contents']['tt_content:1']));
```

#### Structure

The returned structure of a frontend request is an array with some information about your page and its children.

```php
[
    // Page for your request
    'pages:1' => [
        'uid' => '1',
        'pid' => '0',
        'sorting' => '0',
        'title' => 'Root',
        // Array with subpages
        '__pages' => [
            'pages:2' => [
                'uid' => '2',
                'pid' => '1',
                'sorting' => '0',
                'title' => 'Dummy 1-2',
            ],
            'pages:5' => [
                'uid' => '5',
                'pid' => '1',
                'sorting' => '0',
                'title' => 'Dummy 1-5',
            ],
        ],
        // Array with content elements
        '__contents' => [
              'tt_content:1' => [
                  'uid' => '1',
                  'pid' => '1',
                  'sorting' => '0',
                  'header' => 'Test content',
                  'sys_language_uid' => '0',
                  'categories' => '0',
              ],
        ],
    ],
]
```

If you need additional information about a record, you can provide additional TypoScript with the needed configuration.

```php
// Setup the page with uid 1 and include ntf and own TypoScript
$this->setUpFrontendRootPage(
    1,
    array(
        'ntf://TypoScript/JsonRenderer.ts',
        'EXT:example_extension/Tests/Functional/Fixtures/TypoScript/Config.ts'
    )
);
```

Content of the TypoScript file *Config.ts*

```
config.watcher.tableFields.tt_content = uid,_ORIG_uid,_LOCALIZED_UID,pid,sorting,sys_language_uid,header,categories,CType,subheader,bodytext
```
## Additional information

Following links provide documentation and additional information about TYPO3 CMS extension testing

- [Unit tests for dummies](https://de.slideshare.net/cpsitgmbh/unit-tests-for-dummies)
- [How to write functional test](https://wiki.typo3.org/Functional_testing#How_to_write_functional_test.3F)
- [Functional tests with TYPO3](https://de.slideshare.net/cpsitgmbh/functional-tests-with-typo3)
- [Functional tests for dummies](https://de.slideshare.net/cpsitgmbh/functional-tests-for-dummies-65673214)

Last but not least you may ask for further support in the Slack channel "[#cig-testing](https://typo3.slack.com/messages/cig-testing)".
