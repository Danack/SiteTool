<?php

use Auryn\Injector;
use Tier\TierCLIApp;
use Tier\CLIFunction;

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

$app = createApplication();
$injector->share($app);

define('TIER_ROUTING', 10);

$resolver = new \SiteTool\BlockingResolver();

$em = new \SiteTool\DebuggingEventManager();

$injector->share($em);
$injector->alias(\Zend\EventManager\EventManager::class, SiteTool\DebuggingEventManager::class);

\Amp\Dns\resolver($resolver);

$tierApp->addExecutable(TIER_ROUTING, 'Tier\Bridge\ConsoleRouter::routeCommand');

try {
    $tierApp->execute();
}
catch (\Exception $e) {
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString();
    echo "\n";
    
    while(($e = $e->getPrevious()) !== null) {
        echo "Previously:\n";
        echo $e->getTraceAsString();
        echo "\n";
    }
}
