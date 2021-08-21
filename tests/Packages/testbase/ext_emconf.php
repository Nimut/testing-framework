<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "testbase".
 *
 * Auto generated 09-09-2019 14:14
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
  'title' => 'Testbase extension',
  'description' => 'Testbase extension for nimut/testing-framework',
  'category' => 'misc',
  'author' => 'Nicole Cordes, Helmut Hummel',
  'author_email' => 'cordes@cps-it.de, info@helhum.io',
  'author_company' => '',
  'state' => 'stable',
  'version' => '0.6.0',
  'uploadfolder' => 0,
  'createDirs' => '',
  'clearCacheOnLoad' => 0,
  'constraints' =>
  array (
    'depends' =>
    array (
      'typo3' => '9.5.0-11.3.99',
    ),
    'conflicts' =>
    array (
    ),
    'suggests' =>
    array (
    ),
  ),
  '_md5_values_when_last_written' => 'a:22:{s:13:"composer.json";s:4:"0d02";s:12:"ext_icon.png";s:4:"d6a7";s:14:"ext_tables.php";s:4:"debe";s:16:"Classes/Mock.php";s:4:"6a0a";s:44:"Classes/Controller/DeprecationController.php";s:4:"24be";s:48:"Classes/ViewHelpers/RenderChildrenViewHelper.php";s:4:"a4c8";s:35:"Tests/Functional/FunctionalTest.php";s:4:"d9f6";s:74:"Tests/Functional/Controller/DeprecationControllerDisabledExceptionTest.php";s:4:"5a72";s:71:"Tests/Functional/Controller/DeprecationControllerTypo3ExceptionTest.php";s:4:"d44c";s:54:"Tests/Functional/Fixtures/Database/tx_testbase_foo.sql";s:4:"d1fa";s:49:"Tests/Functional/Fixtures/Templates/Template.html";s:4:"01df";s:44:"Tests/Functional/Fixtures/TypoScript/Page.ts";s:4:"3109";s:46:"Tests/Functional/TestSystem/TestSystemTest.php";s:4:"2598";s:61:"Tests/Functional/ViewHelpers/RenderChildrenViewHelperTest.php";s:4:"ce0c";s:36:"Tests/Unit/FileStreamWrapperTest.php";s:4:"ba91";s:23:"Tests/Unit/MockTest.php";s:4:"18a3";s:23:"Tests/Unit/UnitTest.php";s:4:"97dd";s:35:"Tests/Unit/Fixtures/badfilename.php";s:4:"1e75";s:44:"Tests/Unit/Fixtures/ext_typoscript_setup.txt";s:4:"be8d";s:55:"Tests/Unit/Fixtures/recursive_includes_setup.typoscript";s:4:"c5cd";s:36:"Tests/Unit/Fixtures/setup.typoscript";s:4:"fde0";s:55:"Tests/Unit/ViewHelpers/RenderChildrenViewHelperTest.php";s:4:"0807";}',
);
