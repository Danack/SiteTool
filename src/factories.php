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
