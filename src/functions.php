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

    $statsCommand = new Command('hello:world', 'SiteTool\HelloWorld::run');
    $statsCommand->setDescription("Hello world test.");
    $application->add($statsCommand);
    
    $crawlerCommand = new Command('site:crawl', 'SiteTool\Crawler::run');
    $crawlerCommand->setDescription("Crawls a site");
    $crawlerCommand->addArgument('domainName', InputArgument::REQUIRED, 'The domain name to be crawled');
    $crawlerCommand->addOption('jobs', 'j', InputOption::VALUE_OPTIONAL, "How many requests to make at once to a domain", 4);
    $application->add($crawlerCommand);

    
    $migrateCheckCommand = new Command('site:migratecheck', 'SiteTool\MigrateCheck::run');
    $migrateCheckCommand->setDescription("Check that all the urls from an old site are migrated to a new domain correctly.");
    $migrateCheckCommand->addArgument('oldDomainName', InputArgument::REQUIRED, 'The old domain name to be crawled');
    $migrateCheckCommand->addArgument('newDomainName', InputArgument::REQUIRED, 'The new domain name to be crawled');
    $application->add($migrateCheckCommand);

    return $application;
}