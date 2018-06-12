<?php

declare(strict_types=1);

namespace SiteTool\EventProcessor;

interface Relay
{
    public function getAsyncWorkers();
}
