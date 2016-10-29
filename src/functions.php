<?php

use Amp\Artax\Client as ArtaxClient;
use Danack\Console\Application;
use Danack\Console\Command\Command;
use Danack\Console\Input\InputArgument;
use Danack\Console\Input\InputOption;


function getRawCharacters($result)
{
    $resultInHex = unpack('H*', $result);
    $resultInHex = $resultInHex[1];
    $resultSeparated = implode(', ', str_split($resultInHex, 2)); //byte safe
    return $resultSeparated;
}

function createArtaxClient($jobs)
{
    $client = new ArtaxClient();
    $client->setOption(\Amp\Artax\Client::OP_MS_CONNECT_TIMEOUT, 2500);
    $client->setOption(ArtaxClient::OP_HOST_CONNECTION_LIMIT, $jobs);

    return $client;
}

function createApplication()
{
    $application = new Application("SiteTool", "1.0.0");
    
    $crawlerCommand = new Command('site:crawl', 'SiteTool\Crawler::run');
    $crawlerCommand->setDescription("Crawls a site");
    $crawlerCommand->addArgument('initialUrl', InputArgument::REQUIRED, 'The initialUrl to be crawled');
    $crawlerCommand->addOption('jobs', 'j', InputOption::VALUE_OPTIONAL, "How many requests to make at once to a domain", 4);
    // $crawlerCommand->addOption('debug', 'd', InputOption::VALUE_OPTIONAL, "Whether to debug an exception", false);
    $application->add($crawlerCommand);

    $migrateCheckCommand = new Command('site:migratecheck', 'SiteTool\MigrateCheck::run');
    $migrateCheckCommand->setDescription("Check that all the urls from an old site are migrated to a new domain correctly.");
    $migrateCheckCommand->addArgument('oldDomainName', InputArgument::REQUIRED, 'The old domain name to be crawled');
    $migrateCheckCommand->addArgument('newDomainName', InputArgument::REQUIRED, 'The new domain name to be crawled');
    $migrateCheckCommand->addOption('jobs', 'j', InputOption::VALUE_OPTIONAL, "How many requests to make at once to a domain", 4);
    $application->add($migrateCheckCommand);

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

function createFileErrorWriter($errorFilename = null) {

    if ($errorFilename === null) {
        $errorFilename = "error.txt";
    }

    return new SiteTool\ErrorWriter\FileErrorWriter($errorFilename);
}

function createFileResultWriter($resultFilename = null)
{
    if ($resultFilename === null) {
        $resultFilename = "output.txt";
    }

    return new SiteTool\ResultWriter\FileResultWriter($resultFilename);
}

function createFileMigrationResultWriter($resultFilename = null)
{
    if ($resultFilename === null) {
        $resultFilename = 'migration_result.txt';
    }

    return new SiteTool\MigrationResultWriter\FileMigrationResultWriter($resultFilename);
}

function createStandardResultReader($resultFilename = null)
{
    if ($resultFilename === null) {
        $resultFilename = 'output.txt';
    }

    return new \SiteTool\ResultReader\StandardResultReader($resultFilename);
}


function createCrawlerConfig($initialUrl)
{
    $urlParts = parse_url($initialUrl);
    if (array_key_exists('host', $urlParts) === false) {
        echo "Could not determine domain name from " . $initialUrl . "\n";
        exit(-1);
    }

    $domainName = $urlParts['host'];
    $initialPath = '/';

    if (array_key_exists('host', $urlParts) === true) {
        $initialPath = $urlParts['path'];
    }

    return new SiteTool\CrawlerConfig(
        'http',
        $domainName,
        $initialPath
    );
}
