<?php

declare(strict_types=1);

namespace SiteTool\Command;

use Auryn\Injector;
use SiteTool\ProcessSourceList;

class RunTheThings
{
    /** @var Injector  */
    private $injector;

    /** @var  \SiteTool\EventProcessor\Relay[] */
    private $relays;

    public function __construct(Injector $injector)
    {
        $this->injector = $injector;
    }

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
