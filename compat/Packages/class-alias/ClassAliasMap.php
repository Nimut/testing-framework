<?php

return [
    'TYPO3\\Components\\TestingFramework\\Core\\Exception' => \Nimut\TestingFramework\Exception\Exception::class,
    'TYPO3\\Components\\TestingFramework\\Core\\FileStreamWrapper' => \Nimut\TestingFramework\File\FileStreamWrapper::class,
    'TYPO3\\Components\\TestingFramework\\Core\\Functional\\Framework\\Frontend\\Response' => \Nimut\TestingFramework\Http\Response::class,
    'TYPO3\\Components\\TestingFramework\\Core\\Functional\\Framework\\Frontend\\ResponseContent' => \Nimut\TestingFramework\Http\ResponseContent::class,
    'TYPO3\\Components\\TestingFramework\\Core\\Functional\\Framework\\Frontend\\ResponseSection' => \Nimut\TestingFramework\Http\ResponseSection::class,
    'TYPO3\\Components\\TestingFramework\\Core\\Functional\\FunctionalTestCase' => \Nimut\TestingFramework\TestCase\FunctionalTestCase::class,
    'TYPO3\\Components\\TestingFramework\\Core\\Unit\\UnitTestCase' => \Nimut\TestingFramework\TestCase\UnitTestCase::class,
    'TYPO3\\Components\\TestingFramework\\Fluid\\Unit\\ViewHelpers\\ViewHelperBaseTestcase' => \Nimut\TestingFramework\TestCase\ViewHelperBaseTestcase::class,
];
