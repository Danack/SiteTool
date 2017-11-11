<?php


namespace SiteTool\EventProcessor;

use SiteTool\Writer\OutputWriter;
use SiteTool\EventManager;
use SiteTool\Event\FoundUrlToSkip;

class LogSkippedLink
{
    private $skippedHrefs = [];
    
    /** @var OutputWriter */
    private $outputWriter;
    
    public function __construct(
        EventManager $eventManager,
        OutputWriter $outputWriter
    ) {
        $this->outputWriter = $outputWriter;
        $eventManager->attachEvent(FoundUrlToSkip::class, [$this, 'skipping'], 'Log skipped links');
    }

    public function skipping(FoundUrlToSkip $foundUrlToSkip)
    {
        $href = $foundUrlToSkip->href;
        if (array_key_exists($href, $this->skippedHrefs) === true) {
            return;
        }
        $this->skippedHrefs[$href] = true;
        $this->outputWriter->write(
            \SiteTool\Writer\OutputWriter::PROGRESS,
            "skipping" . $foundUrlToSkip->href
        );
    }
}
