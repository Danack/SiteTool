<?php


use Danack\Console\Application;
use Danack\Console\Command\Command;
use Danack\Console\Input\InputArgument;

function createApplication()
{
    $application = new Application("SiteTool", "1.0.0");
    $goCommand = new Command('site:go', 'SiteTool\Command\RunTheThings::run');
    $goCommand->setDescription("GoGoGo");

    $goCommand->addArgument(
        'processSource',
        InputArgument::REQUIRED,
        'The class name that contains the list of items to process'
    );
    $application->add($goCommand);


    $graphCommand = new Command('site:graph', '\SiteTool\Command\GraphTheThings::run');
    $graphCommand->setDescription("GraphTheThings");
    $graphCommand->addArgument(
        'processSource',
        InputArgument::REQUIRED,
        'The class name that contains the list of items to process'
    );
    $application->add($graphCommand);

    return $application;
}

function startsWith($haystack, $needle)
{
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}

function createStandardResultReader(\SiteTool\AppConfig $appConfig)
{
    if ($appConfig->crawlOutput === null) {
        throw new \Exception("CrawlOutput is not configured - can't know how to read crawl results");
    }

    return new \SiteTool\ResultReader\StandardResultReader($appConfig->crawlOutput);
}

function createCrawlerConfig($initialUrl)
{
    $urlParts = parse_url($initialUrl);
    if (array_key_exists('host', $urlParts) === false) {
        echo "Could not determine domain name from " . $initialUrl . "\n";
        echo "Please include the schema like http://example.com \n";
        exit(-1);
    }

    $domainName = $urlParts['host'];
    $initialPath = '/';

    if (array_key_exists('path', $urlParts) === true) {
        $initialPath = $urlParts['path'];
    }

    return new SiteTool\CrawlerConfig(
        'http',
        $domainName,
        $initialPath
    );
}

function normalizeEventName($eventName)
{
    $lastSlashPos = strrpos($eventName, '\\');
    if ($lastSlashPos !== false) {
        return substr($eventName, $lastSlashPos + 1);
    }
    return $eventName;
}

function ampRunAllTheThings($workers, $setupCallable)
{
    $coroutines = [];

    foreach ($workers as $worker) {
        $coroutines[] = \Amp\coroutine($worker);
    }

    \Amp\Loop::run(function () use ($coroutines, $setupCallable) {
        $setupCallable();
        $runningThings = [];

        foreach ($coroutines as $coroutine) {
            $runningThings[] = $coroutine();
        }

        yield $runningThings;

        // Because we automatically exit the event loop, this line will never be reached.
        // If some end condition is known, the while (true) in the workers can be replaced
        // and then this line will be reached once finished.
        print "Finished processing." . PHP_EOL; // <-- Never reached
    });
}
