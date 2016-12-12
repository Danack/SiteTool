<?php


namespace SiteTool\Processor;

use SiteTool\Writer\OutputWriter;
use SiteTool\Event\SkippingLinkWatcherEvent;

class SkippingLinkWatcher
{
    private $skippedHrefs = [];

    public function __construct(
        OutputWriter $outputWriter,
        $skippingLinkEvent
    ) {
        $this->outputWriter = $outputWriter;    
    }

    public function skippingLink(
        $href,
        $host,
        SkippingLinkWatcherEvent $skippingLinkWatcherEvent
    ) {
        if (array_key_exists($href, $this->skippedHrefs) === true) {
            return;
        }
        $this->skippedHrefs[$href] = true;
        $skippingLinkWatcherEvent->skipping("Skipping $href as host $host is different.");
        $this->outputWriter->write(
            OutputWriter::PROGRESS,
            "Skipping $href as host $host is different."
        );
    }
}
