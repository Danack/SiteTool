<?php

use Auryn\Injector;
use Tier\TierCLIApp;
use Tier\CLIFunction;

ini_set('display_errors', 'on');

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
$tierApp->execute();
