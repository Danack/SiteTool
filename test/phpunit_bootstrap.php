<?php

use Auryn\Injector;

$autoloader = require(__DIR__.'/../vendor/autoload.php');
// $autoloader->add('Fixtures', [__DIR__.'/fixtures/']);

define("TEMP_PATH", __DIR__ . "/temp");

function createInjector()
{
    /** @var  $injectionParams \AurynConfig\InjectionParams::__construct */
    $injectionParams = require_once __DIR__ . "/../src/testInjectionParams.php";
    $injector = new Injector();
    $injectionParams->addToInjector($injector);

    return $injector;
}
