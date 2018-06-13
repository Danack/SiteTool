<?php

declare(strict_types=1);

namespace SiteTool\Command;

use SiteTool\ProcessSourceList;
use function SiteTool\ampRunAllTheThings;

class RunTheThings
{
    /** @var  \SiteTool\EventProcessor\Relay[] */
    private $relays;

    public function run(ProcessSourceList $processSourceList)
    {
        $this->relays = $processSourceList->getEventProcessors();
        $workers = [];

        foreach ($this->relays as $relay) {
            $workers += $relay->getAsyncWorkers();
        }

        ampRunAllTheThings($workers, $processSourceList->getSetupFunction());

        $processSourceList->endOfProcessing();
    }
}
