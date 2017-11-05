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
    $crawlerCommand->addOption('graph', 'g', InputOption::VALUE_NONE, "Instead of executing, diagram the apps events", null);
    $crawlerCommand->addOption('crawlOutput', null, InputOption::VALUE_OPTIONAL, "Where to send error output. Allowed values null, stdout, stderr, or a filename", "crawl_result.txt");
    addOutputOptionsToCommand($crawlerCommand);
    $application->add($crawlerCommand);



    $crawlerCommand = new Command('site:debug', 'SiteTool\Command\Debug::run');
    $crawlerCommand->setDescription("Debugs new functionality");
    $crawlerCommand->addArgument('initialUrl', InputArgument::REQUIRED, 'The initialUrl to be crawled');
    $crawlerCommand->addOption('jobs', 'j', InputOption::VALUE_OPTIONAL, "How many requests to make at once to a domain", 4);
    $crawlerCommand->addOption('graph', 'g', InputOption::VALUE_NONE, "Instead of executing, diagram the apps events", null);
    $crawlerCommand->addOption('crawlOutput', null, InputOption::VALUE_OPTIONAL, "Where to send error output. Allowed values null, stdout, stderr, or a filename", "crawl_result.txt");
    addOutputOptionsToCommand($crawlerCommand);
    $application->add($crawlerCommand);




    $crawlerCommand = new Command('site:crawl_validate', 'SiteTool\Command\CrawlerWithValidator::run');
    $crawlerCommand->setDescription("Crawls a site and validates against the w3c");
    $crawlerCommand->addArgument('initialUrl', InputArgument::REQUIRED, 'The initialUrl to be crawled');
    $crawlerCommand->addOption('jobs', 'j', InputOption::VALUE_OPTIONAL, "How many requests to make at once to a domain", 4);
    $crawlerCommand->addOption('graph', 'g', InputOption::VALUE_NONE, "Instead of executing, diagram the apps events", null);
    $crawlerCommand->addOption('crawlOutput', null, InputOption::VALUE_OPTIONAL, "Where to send error output. Allowed values null, stdout, stderr, or a filename", "crawl_result.txt");
    addOutputOptionsToCommand($crawlerCommand);
    $application->add($crawlerCommand);


    $statusCheckCommand = new Command('site:check', 'SiteTool\Command\Check::run');
    $statusCheckCommand->setDescription("Check that all the urls from a site are still ok.");
    $statusCheckCommand->addOption('jobs', 'j', InputOption::VALUE_OPTIONAL, "How many requests to make at once to a domain", 4);
    $statusCheckCommand->addOption('crawlOutput', null, InputOption::VALUE_OPTIONAL, "Where to send error output. Allowed values null, stdout, stderr, or a filename", "crawl_result.txt");
    $statusCheckCommand->addOption('checkOutput', null, InputOption::VALUE_OPTIONAL, "Where to send check output. Allowed values null, stdout, stderr, or a filename", 'check_result.txt');
    addOutputOptionsToCommand($statusCheckCommand);
    $application->add($statusCheckCommand);

//    $migrateCheckCommand = new Command('site:migratecheck', 'SiteTool\Command\MigrateCheck::run');
//    $migrateCheckCommand->setDescription("Check that all the urls from an old site are migrated to a new domain correctly.");
//    $migrateCheckCommand->addArgument('oldDomainName', InputArgument::REQUIRED, 'The old domain name to be crawled');
//    $migrateCheckCommand->addArgument('newDomainName', InputArgument::REQUIRED, 'The new domain name to be crawled');
//    $migrateCheckCommand->addOption('jobs', 'j', InputOption::VALUE_OPTIONAL, "How many requests to make at once to a domain", 4);
//    $migrateCheckCommand->addOption('crawlOutput', null, InputOption::VALUE_OPTIONAL, "Where read the crawl result from. Allowed values null, stdout, stderr, file", 'crawl_result.txt');
//    $migrateCheckCommand->addOption('migrationOutput', null, InputOption::VALUE_OPTIONAL, "Where to send migration check output. Allowed values null, stdout, stderr, or a filename", 'migration_result.txt');
//    
//    addOutputOptionsToCommand($migrateCheckCommand, false);
//    $application->add($migrateCheckCommand);

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
