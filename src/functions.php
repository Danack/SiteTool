<?php

use Amp\Artax\Client as ArtaxClient;

function createArtaxClient()
{
    $client = new ArtaxClient();
    $client->setOption(\Amp\Artax\Client::OP_MS_CONNECT_TIMEOUT, 2500);
    $client->setOption(ArtaxClient::OP_HOST_CONNECTION_LIMIT, 4);

    return $client;
}