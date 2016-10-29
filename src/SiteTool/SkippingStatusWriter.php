<?php


namespace SiteTool;

use Zend\EventManager\EventManager;
use Zend\EventManager\Event;
use SiteTool\StatusWriter;

class SkippingStatusWriter
{
    private $skippedHrefs = [];
    
    public function __construct(
        EventManager $eventManager,
        Rules $rules,
        StatusWriter $statusWriter,
        $debug
    ) {
        $this->rules = $rules;
        $this->eventManager = $eventManager;
        $this->statusWriter = $statusWriter;
        $this->debug = $debug;
        
        $eventManager->attach(SiteChecker::SKIPPING_LINK_DUE_TO_DOMAIN, [$this, 'skippingLinkEvent']);    
    }

    public function skippingLinkEvent(Event $e)
    {
        if ($this->debug) {
            // DJA This scares and confuses me.
            echo "Where is my exception?";
            throw new \Exception("Something went wrong\n");
        }
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
