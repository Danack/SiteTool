<?php

use Auryn\Injector;
use Tier\TierCLIApp;
use Tier\CLIFunction;

use SiteTool\ErrorWriter;

ini_set('display_errors', 'on');
error_reporting(E_ALL);

require_once(__DIR__.'/../vendor/autoload.php');

CLIFunction::setupErrorHandlers();

$injector = new Injector();

$standardInjectionParams = require __DIR__."/../src/injectionParams.php";

/** @var $injectionParams \AurynConfig\InjectionParams */
$standardInjectionParams->addToInjector($injector);

$cliInjectionParams = require __DIR__."/cliInjectionParams.php";
/** @var $cliInjectionParams \AurynConfig\InjectionParams */
$cliInjectionParams->addToInjector($injector);
$tierApp = new TierCLIApp($injector);

define('TIER_ROUTING', 10);

$tierApp->addExecutable(TIER_ROUTING, 'Tier\Bridge\ConsoleRouter::routeCommand');

try {
    $tierApp->execute();
    // $injector->execute('Tier\Bridge\ConsoleRouter::routeCommand');
}
catch (\Exception $e) {
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString();
    echo "\n";
    
    while(($e = $e->getPrevious()) !== null) {
        echo "Previouly:\n";
        echo $e->getTraceAsString();
        echo "\n";
    }
}
