<?php

use AurynConfig\InjectionParams;


// These classes will only be created  by the injector once
$shares = [
    SiteTool\SiteChecker::class,
    SiteTool\Rules::class,
    SiteTool\Writer\CrawlResultWriter::class,
    SiteTool\Writer\ErrorWriter::class,
    SiteTool\Writer\OutputWriter::class,
    SiteTool\Writer\MigrationResultWriter::class,
    SiteTool\Writer\StatusWriter::class,
    SiteTool\CrawlerConfig::class,
    SiteTool\Writer\FileWriter::class,
    SiteTool\Writer\NullWriter::class,
    SiteTool\Writer\StderrWriter::class,
    SiteTool\Writer\StdoutWriter::class,
    
    
    SiteTool\CrawlerConfig::class,
    SiteTool\ResultReader\StandardResultReader::class,

    Zend\EventManager\EventManager::class,
];

// Alias interfaces (or classes) to the actual types that should be used 
// where they are required. 
$aliases = [
    SiteTool\ResultReader::class => SiteTool\ResultReader\StandardResultReader::class,
    SiteTool\Writer\OutputWriter::class => SiteTool\Writer\OutputWriter\StandardOutputWriter::class,
];

// Delegate the creation of types to callables.
$delegates = [
    Danack\Console\Application::class => 'createApplication',
    
    SiteTool\CrawlerConfig::class => 'createCrawlerConfig',
    SiteTool\ResultReader\StandardResultReader::class => 'createStandardResultReader',
    
    SiteTool\Writer\ErrorWriter::class => 'createErrorWriter',
    SiteTool\Writer\CrawlResultWriter::class => 'createCrawlResultWriter',
    SiteTool\Writer\MigrationResultWriter::class => 'createMigrationResultWriter',
    SiteTool\Writer\StatusWriter::class => 'createStatusWriter',
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
