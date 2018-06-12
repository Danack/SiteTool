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
        $processorsToCreate = $processSourceList->getProcessList();

        foreach ($processorsToCreate as $relayToCreate) {
            // This just holds a references to the object, to stop
            // it from being GC'd.
            $this->relays[] = $this->injector->make($relayToCreate);
        }

        $workers = [];

        foreach ($this->relays as $relay) {
            $workers += $relay->getAsyncWorkers();
        }

        ampRunAllTheThings($workers, $processSourceList->getSetupFunction());

        $processSourceList->endOfProcessing();
    }
}
