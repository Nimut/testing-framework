<?php
$autoloadFilepath = '{ntfRoot}../../autoload.php';
if (!file_exists($autoloadFilepath)) {
    $autoloadFilepath = '{ntfRoot}.Build/vendor/autoload.php';
}
require $autoloadFilepath;
\Nimut\TestingFramework\Frontend\RequestBootstrap::setGlobalVariables({arguments});
\Nimut\TestingFramework\Frontend\RequestBootstrap::executeAndOutput();
