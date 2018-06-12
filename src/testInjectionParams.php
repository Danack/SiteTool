<?php

use AurynConfig\InjectionParams;

// These classes will only be created  by the injector once
$shares = [
//    SiteTool\SiteChecker::class,
//    \SiteTool\EventProcessor\Rules::class,
//    SiteTool\ErrorWriter::class,
//    SiteTool\StatusWriter::class,
//    SiteTool\ResultWriter::class,
//    Zend\EventManager\EventManager::class,
//    SiteTool\CrawlerConfig::class,
];

// Alias interfaces (or classes) to the actual types that should be used 
// where they are required. 
$aliases = [
//    SiteTool\StatusWriter::class => SiteTool\StatusWriter\StdoutStatusWriter::class,
//    //SiteTool\ResultWriter::class => SiteTool\ResultWriter\FileResultWriter::class,
//    SiteTool\ResultReader::class => SiteTool\ResultReader\StandardResultReader::class,
//
//    SiteTool\StatusWriter::class => SiteTool\StatusWriter\NullStatusWriter::class,
//    SiteTool\ErrorWriter::class => SiteTool\ErrorWriter\NullErrorWriter::class,
];

// Delegate the creation of types to callables.
$delegates = [
    Danack\Console\Application::class => 'createApplication'
];

// If necessary, define some params per class.
$classParams = [
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
