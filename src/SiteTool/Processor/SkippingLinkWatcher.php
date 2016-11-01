<?php


namespace SiteTool\Processor;

use SiteTool\Rules;
use SiteTool\SiteChecker;
use Zend\EventManager\EventManager;
use Zend\EventManager\Event;
use SiteTool\Writer\StatusWriter;

class SkippingLinkWatcher
{
    private $skippedHrefs = [];
    
    public function __construct(
        EventManager $eventManager,
        Rules $rules,
        StatusWriter $statusWriter
    ) {
        $this->rules = $rules;
        $this->eventManager = $eventManager;
        $this->statusWriter = $statusWriter;

        $eventManager->attach(SiteChecker::SKIPPING_LINK_DUE_TO_DOMAIN, [$this, 'skippingLinkEvent']);    
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
        $this->statusWriter->write("Skipping $href as host $host is different.");
    }
}
