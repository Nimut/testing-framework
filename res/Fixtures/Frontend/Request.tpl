<?php
$autoloadFilepath = '{ntfRoot}../../autoload.php';
if (!file_exists($autoloadFilepath)) {
    $autoloadFilepath = '{ntfRoot}.Build/vendor/autoload.php';
}
require $autoloadFilepath;
\Nimut\TestingFramework\Bootstrap\RequestBootstrap::setGlobalVariables({arguments});
\Nimut\TestingFramework\Bootstrap\RequestBootstrap::executeAndOutput();
