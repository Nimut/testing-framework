# Testing Framework for TYPO3 CMS Extensions

[![Latest Stable Version](https://img.shields.io/packagist/v/nimut/testing-framework.svg)](https://packagist.org/packages/nimut/testing-framework)
[![Build Status](https://img.shields.io/travis/Nimut/TYPO3-testing-framework/master.svg)](https://travis-ci.org/Nimut/TYPO3-testing-framework)
[![StyleCI](https://styleci.io/repos/81999184/shield?branch=master)](https://styleci.io/repos/81999184)

The aim of the testing framework is to provide a good way to write and run unit and functional tests for multiple versions
of the TYPO3 CMS. Currently **TYPO3 CMS 6.2 up to master (8.6)** are tested and supported.

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
