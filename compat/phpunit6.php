<?php


//phpunit 6 compatibility
//used from codeception shim.php
if (!class_exists('PHPUnit_Framework_TestCase') && class_exists('PHPUnit\Framework\TestCase')) {
    class_alias('PHPUnit\Framework\Assert', 'PHPUnit_Framework_Assert');
    class_alias('PHPUnit\Framework\AssertionFailedError', 'PHPUnit_Framework_AssertionFailedError');
    class_alias('PHPUnit\Framework\Constraint\Constraint', 'PHPUnit_Framework_Constraint');
    class_alias('PHPUnit\Framework\Constraint\LogicalNot', 'PHPUnit_Framework_Constraint_Not');
    class_alias('PHPUnit\Framework\DataProviderTestSuite', 'PHPUnit_Framework_TestSuite_DataProvider');
    class_alias('PHPUnit\Framework\Exception', 'PHPUnit_Framework_Exception');
    class_alias('PHPUnit\Framework\ExceptionWrapper', 'PHPUnit_Framework_ExceptionWrapper');
    class_alias('PHPUnit\Framework\ExpectationFailedException', 'PHPUnit_Framework_ExpectationFailedException');
    class_alias('PHPUnit\Framework\IncompleteTestError', 'PHPUnit_Framework_IncompleteTestError');
    class_alias('PHPUnit\Framework\SelfDescribing', 'PHPUnit_Framework_SelfDescribing');
    class_alias('PHPUnit\Framework\SkippedTestError', 'PHPUnit_Framework_SkippedTestError');
    class_alias('PHPUnit\Framework\Test', 'PHPUnit_Framework_Test');
    class_alias('PHPUnit\Framework\TestCase', 'PHPUnit_Framework_TestCase');
    class_alias('PHPUnit\Framework\TestFailure', 'PHPUnit_Framework_TestFailure');
    class_alias('PHPUnit\Framework\TestListener', 'PHPUnit_Framework_TestListener');
    class_alias('PHPUnit\Framework\TestResult', 'PHPUnit_Framework_TestResult');
    class_alias('PHPUnit\Framework\TestSuite', 'PHPUnit_Framework_TestSuite');
    class_alias('PHPUnit\Framework\Warning', 'PHPUnit_Framework_Warning');
    class_alias('PHPUnit\Runner\BaseTestRunner', 'PHPUnit_Runner_BaseTestRunner');
    class_alias('PHPUnit\Runner\Filter\Factory', 'PHPUnit_Runner_Filter_Factory');
    class_alias('PHPUnit\Runner\Filter\NameFilterIterator', 'PHPUnit_Runner_Filter_Test');
    class_alias('PHPUnit\Runner\Filter\IncludeGroupFilterIterator', 'PHPUnit_Runner_Filter_Group_Include');
    class_alias('PHPUnit\Runner\Filter\ExcludeGroupFilterIterator', 'PHPUnit_Runner_Filter_Group_Exclude');
    class_alias('PHPUnit\Runner\Version', 'PHPUnit_Runner_Version');
    class_alias('PHPUnit\TextUI\ResultPrinter', 'PHPUnit_TextUI_ResultPrinter');
    class_alias('PHPUnit\TextUI\TestRunner', 'PHPUnit_TextUI_TestRunner');
    class_alias('PHPUnit\Util\Log\JUnit', 'PHPUnit_Util_Log_JUnit');
    class_alias('PHPUnit\Util\Printer', 'PHPUnit_Util_Printer');
    class_alias('PHPUnit\Util\Test', 'PHPUnit_Util_Test');
    class_alias('PHPUnit\Util\TestDox\ResultPrinter', 'PHPUnit_Util_TestDox_ResultPrinter');
    class_alias('PHPUnit\Framework\BaseTestListener', 'PHPUnit_Framework_BaseTestListener');
    class_alias('PHPUnit\Util\PHP\AbstractPhpProcess', 'PHPUnit_Util_PHP');
}