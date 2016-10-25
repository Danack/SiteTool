<?php

use AurynConfig\InjectionParams;

// These classes will only be created  by the injector once
$shares = [
    SiteTool\StatusWriter::class,
    SiteTool\ResultWriter::class
];

// Alias interfaces (or classes) to the actual types that should be used 
// where they are required. 
$aliases = [
    //'ArtaxServiceBuilder\ResponseCache' => 'ArtaxServiceBuilder\ResponseCache\FileResponseCache',
    'Danack\Console\Application' => 'SiteTool\ConsoleApplication',
    SiteTool\StatusWriter::class => SiteTool\StatusWriter\StdoutStatusWriter::class,
    SiteTool\ResultWriter::class => SiteTool\ResultWriter\FileResultWriter::class
];

// Delegate the creation of types to callables.
$delegates = [
    //'ArtaxServiceBuilder\Oauth2Token' => 'ServerContainer\App::getOauthToken',
    //'Amp\Reactor' => 'Amp\getReactor',
    //'ServerContainer\Tool\EC2Manager' => 'ServerContainer\App::createEC2Manager',
    //'ServerContainer\Tool\KillEC2TestInstances' => 'ServerContainer\App::createKillEC2TestInstances'
];


// If necessary, define some params per class.
$classParams = [
//    'cacheDirectory' => realpath(__DIR__."/../var/cache"),
//    'tempDirectory' => realpath(__DIR__."/../var/tmp"),
//    'userAgent' => 'Danack/ServerContainer'

    SiteTool\ResultWriter\FileResultWriter::class => [
        ':filename' => 'output.txt'
    ]
];


// If necessary, define some params that can be injected purely by name.
$defines = [
    'maxCount' => 50000,
];

$prepares = [
    
];

$injectionParams = new InjectionParams(
    $shares,
    $aliases,
    $delegates,
    $classParams,
    $prepares,
    $defines
);

return $injectionParams;
