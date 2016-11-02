<?php


namespace SiteTool\Processor;

use SiteTool\Rules;
use SiteTool\SiteChecker;
use Zend\EventManager\EventManager;
use Zend\EventManager\Event;
//use SiteTool\Writer\StatusWriter;
use SiteTool\Writer\OutputWriter;

class SkippingLinkWatcher
{
    private $skippedHrefs = [];
    
    public function __construct(
        EventManager $eventManager,
        Rules $rules,
        //StatusWriter $statusWriter,
        OutputWriter $outputWriter,
        $skippingLinkEvent
    ) {
        $this->rules = $rules;
        $this->eventManager = $eventManager;
        $this->outputWriter = $outputWriter;

        $eventManager->attach($skippingLinkEvent, [$this, 'skippingLinkEvent']);    
    }

    public function skippingLinkEvent(Event $e)
    {
        $params = $e->getParams();
        $this->skippingLink($params[0], $params[1]);
    }

    public function skippingLink($href, $host)
    {
        if (array_key_exists($href, $this->skippedHrefs) === true) {
            return;
        }

        $this->skippedHrefs[$href] = true;
        $this->outputWriter->write(
            OutputWriter::PROGRESS,
            "Skipping $href as host $host is different."
        );
    }
}
