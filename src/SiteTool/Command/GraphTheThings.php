<?php

declare(strict_types=1);

namespace SiteTool\Command;

use Auryn\Injector;
use SiteTool\GraphVizBuilder;
use SiteTool\ProcessSourceList;

class GraphTheThings
{
    public function run(ProcessSourceList $processSourceList, GraphVizBuilder $graphVizBuilder)
    {
        $relays = $processSourceList->getEventProcessors();
        $graphVizBuilder->finalize();
    }
}
