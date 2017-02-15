# Testing Framework for TYPO3 CMS Extensions

## Installation

Use [Composer](https://getcomposer.org/) to install the testing framework.

```bash
$ composer require --dev nimut/testing-framework
```

Composer will add the package as a dev requirement to your composer.json and install PHPUnit and vfsStream as its
dependencies.

## Usage

To execute the PHPUnit tests of your extension run

```bash
$ vendor/bin/phpunit -c vendor/nimut/testing-framework/res/Configuration/UnitTests.xml \
    typo3conf/ext/example_extension/Tests/Unit
```
