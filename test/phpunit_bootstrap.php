<?php

use Auryn\Injector;

$autoloader = require(__DIR__.'/../vendor/autoload.php');
// $autoloader->add('Fixtures', [__DIR__.'/fixtures/']);

function createInjector()
{
    /** @var  $injectionParams \AurynConfig\InjectionParams::__construct */
    $injectionParams = require_once __DIR__ . "/../src/testInjectionParams.php";
    $injector = new Injector();
    $injectionParams->addToInjector($injector);

    return $injector;
}
