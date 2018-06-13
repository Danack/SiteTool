<?php

declare(strict_types=1);

use Amp\Artax\DefaultClient as ArtaxClient;
use Auryn\Injector;

/**
 * @param int $jobs
 * @return ArtaxClient
 * @throws Error
 */
function createArtaxClient(int $jobs)
{
    $client = new ArtaxClient();
    // $client->setOption(\Amp\Artax\Client::OP_MS_CONNECT_TIMEOUT, 2500);
    // $client->setOption(ArtaxClient::OP_HOST_CONNECTION_LIMIT, $jobs);

    return $client;
}

function createProcessSourceList(Injector $injector, $processSource)
{
    try {
        return $injector->make($processSource);
    }
    catch (\Auryn\InjectionException $ie) {
        $message = sprintf(
            "Failed to create class : %s %s \t %s",
            $processSource,
            $ie->getMessage(),
            implode(", ", $ie->getDependencyChain())
        );

        throw new \SiteTool\SiteToolException($message, 0, $ie);
    }
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

function createStandardResultReader(\SiteTool\AppConfig $appConfig)
{
    if ($appConfig->crawlOutput === null) {
        throw new \Exception("CrawlOutput is not configured - can't know how to read crawl results");
    }

    return new \SiteTool\ResultReader\StandardResultReader($appConfig->crawlOutput);
}
