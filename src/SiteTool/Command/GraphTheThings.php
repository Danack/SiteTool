<?php

declare(strict_types=1);

namespace SiteTool\Command;

use Auryn\Injector;
use SiteTool\GraphVizBuilder;
use SiteTool\ProcessSourceList;

class GraphTheThings
{
    /** @var Injector  */
    private $injector;

    /** @var  \SiteTool\EventProcessor\Relay[] */
    private $relays;

    public function __construct(Injector $injector)
    {
        $this->injector = $injector;
    }

    public function run(ProcessSourceList $processSourceList, GraphVizBuilder $graphVizBuilder)
    {
        $processorsToCreate = $processSourceList->getProcessList();

        foreach ($processorsToCreate as $relayToCreate) {
            // This just holds a references to the object, to stop
            // it from being GC'd.
            $this->relays[] = $this->injector->make($relayToCreate);
        }

        $graphVizBuilder->finalize();
    }
}
