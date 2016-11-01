<?php

use Amp\Artax\Client as ArtaxClient;
use Auryn\Injector;
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

function addOutputOptionsToCommand(Command $command)
{
    $command->addOption('statusOutput', null, InputOption::VALUE_OPTIONAL, "Where to send status output. Allowed values null, stdout, stderr, or a filename", 'stdout');
    $command->addOption('errorOutput', null, InputOption::VALUE_OPTIONAL, "Where to send error output. Allowed values null, stdout, stderr, or a filename", "error.txt");
}

function createApplication()
{
    $application = new Application("SiteTool", "1.0.0");

    $crawlerCommand = new Command('site:crawl', 'SiteTool\Command\Crawler::run');
    $crawlerCommand->setDescription("Crawls a site");
    $crawlerCommand->addArgument('initialUrl', InputArgument::REQUIRED, 'The initialUrl to be crawled');
    $crawlerCommand->addOption('jobs', 'j', InputOption::VALUE_OPTIONAL, "How many requests to make at once to a domain", 4);
    $crawlerCommand->addOption('crawlOutput', null, InputOption::VALUE_OPTIONAL, "Where to send error output. Allowed values null, stdout, stderr, or a filename", "crawl_result.txt");
    $crawlerCommand->addOption('migrationOutput', null, InputOption::VALUE_OPTIONAL, "Where to send migration check output. Allowed values null, stdout, stderr, or a filename", 'migration_result.txt');
    $crawlerCommand->addOption('checkOutput', null, InputOption::VALUE_OPTIONAL, "Where to send check output. Allowed values null, stdout, stderr, or a filename", 'check_result.txt');
    addOutputOptionsToCommand($crawlerCommand);
    $application->add($crawlerCommand);
    
    $statusCheckCommand = new Command('site:check', 'SiteTool\Command\Check::run');
    $statusCheckCommand->setDescription("Check that all the urls from a site are still ok.");
    $statusCheckCommand->addOption('jobs', 'j', InputOption::VALUE_OPTIONAL, "How many requests to make at once to a domain", 4);
    $statusCheckCommand->addOption('crawlOutput', null, InputOption::VALUE_OPTIONAL, "Where to send error output. Allowed values null, stdout, stderr, or a filename", "crawl_result.txt");
    $statusCheckCommand->addOption('migrationOutput', null, InputOption::VALUE_OPTIONAL, "Where to send migration check output. Allowed values null, stdout, stderr, or a filename", 'migration_result.txt');
    $statusCheckCommand->addOption('checkOutput', null, InputOption::VALUE_OPTIONAL, "Where to send check output. Allowed values null, stdout, stderr, or a filename", 'check_result.txt');
    addOutputOptionsToCommand($statusCheckCommand);
    $application->add($statusCheckCommand);

    $migrateCheckCommand = new Command('site:migratecheck', 'SiteTool\Command\MigrateCheck::run');
    $migrateCheckCommand->setDescription("Check that all the urls from an old site are migrated to a new domain correctly.");
    $migrateCheckCommand->addArgument('oldDomainName', InputArgument::REQUIRED, 'The old domain name to be crawled');
    $migrateCheckCommand->addArgument('newDomainName', InputArgument::REQUIRED, 'The new domain name to be crawled');
    $migrateCheckCommand->addOption('jobs', 'j', InputOption::VALUE_OPTIONAL, "How many requests to make at once to a domain", 4);
    $migrateCheckCommand->addOption('statusOutput', null, InputOption::VALUE_OPTIONAL, "Where to send status output. Allowed values null, stdout, stderr, file", 'stdout');
    $migrateCheckCommand->addOption('crawlOutput', null, InputOption::VALUE_OPTIONAL, "Where read the crawl result from. Allowed values null, stdout, stderr, file", 'crawl_result.txt');
    $migrateCheckCommand->addOption('checkOutput', null, InputOption::VALUE_OPTIONAL, "Where to send check output. Allowed values null, stdout, stderr, or a filename", 'check_result.txt');
    $migrateCheckCommand->addOption('migrationOutput', null, InputOption::VALUE_OPTIONAL, "Where to send migration check output. Allowed values null, stdout, stderr, or a filename", 'migration_result.txt');
    
    addOutputOptionsToCommand($migrateCheckCommand, false);
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

function createStandardResultReader($crawlOutput)
{
    return new \SiteTool\ResultReader\StandardResultReader($crawlOutput);
}


function createFileWriter($filename)
{
    static $fileWritersByPath = [];
    
    if (array_key_exists($filename, $fileWritersByPath) === true) {
        return $this->fileWritersByPath[$filename];
    }
    $fileWriter = new \SiteTool\Writer\FileWriter($filename);
    $fileWritersByPath[$filename] = $fileWriter;
    
    return $fileWriter;
}


function createWriter($outputTypeOrFilename)
{
    switch ($outputTypeOrFilename) {
        case 'null':
            return new \SiteTool\Writer\NullWriter();
        case 'stdout':
            return new SiteTool\Writer\StdoutWriter();
        case 'stderr':
            return new \SiteTool\Writer\StderrWriter();
    }

    return createFileWriter($outputTypeOrFilename);
}

function createStatusWriter($statusOutput)
{
    $writer = createWriter($statusOutput);
    
    return new \SiteTool\Writer\StatusWriter($writer);
}

function createErrorWriter($errorOutput)
{
    $writer = createWriter($errorOutput);

    return new \SiteTool\Writer\ErrorWriter($writer);
}

function createCheckResultWriter($checkOutput)
{
    $writer = createWriter($checkOutput);

    return new \SiteTool\Writer\CheckResultWriter($writer);
}

function createCrawlResultWriter($crawlOutput)
{
    $writer = createWriter($crawlOutput);

    return new \SiteTool\Writer\CrawlResultWriter($writer);
}

function createMigrationResultWriter($migrationOutput)
{
    $writer = createWriter($migrationOutput);

    return new \SiteTool\Writer\MigrationResultWriter($writer);
}


function createCrawlerConfig($initialUrl)
{
    $urlParts = parse_url($initialUrl);
    if (array_key_exists('host', $urlParts) === false) {
        echo "Could not determine domain name from " . $initialUrl . "\n";
        echo "Please include the schema like http://example.com";
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
