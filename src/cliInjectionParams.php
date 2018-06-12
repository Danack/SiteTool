<?php

use AurynConfig\InjectionParams;

// These classes will only be created  by the injector once
$shares = [
    SiteTool\CrawlerConfig::class,
    SiteTool\Writer\FileWriter::class,
    SiteTool\Writer\NullWriter::class,
    SiteTool\Writer\StderrWriter::class,
    SiteTool\Writer\StdoutWriter::class,
    SiteTool\CrawlerConfig::class,
    SiteTool\ResultReader\StandardResultReader::class,
    SiteTool\GraphVizBuilder::class,

    Zend\EventManager\EventManager::class,
];

// Alias interfaces (or classes) to the actual types that should be used 
// where they are required. 
$aliases = [
    SiteTool\ResultReader::class => SiteTool\ResultReader\StandardResultReader::class,
    SiteTool\Writer\OutputWriter::class => SiteTool\Writer\OutputWriter\StandardOutputWriter::class,
    SiteTool\EventManager::class => SiteTool\EventManager\StandardEventManager::class
];

// Delegate the creation of types to callables.
$delegates = [
    SiteTool\CrawlerConfig::class => 'createCrawlerConfig',
    SiteTool\ResultReader\StandardResultReader::class => 'createStandardResultReader',
    SiteTool\ProcessSourceList::class => 'createProcessSourceList',
    Amp\Artax\Client::class => 'createArtaxClient',
];

// If necessary, define some params per class.
$classParams = [
];


// If necessary, define some params that can be injected purely by name.
$defines = [
    'maxCount' => 50000,
    'jobs' => 4,
    'initialUrl' => 'http://phpimagick.com',
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
