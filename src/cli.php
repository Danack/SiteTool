<?php

use Auryn\Injector;
use Tier\TierCLIApp;
use Tier\CLIFunction;

ini_set('display_errors', 'on');

require_once(__DIR__.'/../vendor/autoload.php');

CLIFunction::setupErrorHandlers();

// We're on the CLI - let errors be seen.
// ini_set('display_errors', 'off');

//require_once __DIR__.'/../../clavis.php';

$injector = new Injector();

$standardInjectionParams = require __DIR__."/../src/injectionParams.php";
/** @var $injectionParams \AurynConfig\InjectionParams */
$standardInjectionParams->addToInjector($injector);

$cliInjectionParams = require __DIR__."/cliInjectionParams.php";
/** @var $cliInjectionParams \AurynConfig\InjectionParams */
$cliInjectionParams->addToInjector($injector);

//$injector->alias('Danack\Console\Application', 'ImagickDemo\ConsoleApplication');
//$exceptionResolver = TierCLIApp::createStandardExceptionResolver();
//$exceptionResolver->addExceptionHandler(
//    'ServerContainer\UserErrorMessageException',
//    ['ServerContainer\App', 'handleUserErrorMessageException']
//);
//
//$exceptionResolver->addExceptionHandler(
//    'ServerContainer\ServerContainerException',
//    ['ServerContainer\App', 'handleServerContainerException']
//);

$tierApp = new TierCLIApp(
    $injector
);


define('TIER_ROUTING', 10);

$tierApp->addExecutable(TIER_ROUTING, 'Tier\Bridge\ConsoleRouter::routeCommand');
$tierApp->execute();
