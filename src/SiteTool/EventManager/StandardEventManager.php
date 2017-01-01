<?php

namespace SiteTool\EventManager;

use SiteTool\EventManager;
use SiteTool\GraphVizBuilder;
use Zend\EventManager\EventManager as ZendEventManager;

class StandardEventManager implements EventManager
{
    /** @var ZendEventManager  */
    private $zendEventManager;
    
    /** @var GraphVizBuilder  */
    private $graphVizTest;
    
    public function __construct(
        ZendEventManager $zendEventManager,
        GraphVizBuilder $graphVizTest
    ) {
        $this->zendEventManager = $zendEventManager;
        $this->graphVizTest = $graphVizTest;
    }

    public function attachEvent($dataType, callable $listener, $listenerName)
    {
        $fn = function (\Zend\EventManager\Event $event) use ($listener) {
            list($data) = $event->getParams();
            $listener($data);
        };

        $this->zendEventManager->attach($dataType, $fn);
        $this->graphVizTest->addEventListener($dataType, $listenerName);
    }

    public function createTrigger($eventName, $callerName)
    {
        $this->graphVizTest->addEventTrigger($eventName, $callerName);
        $fn = function ($dataType) use ($eventName) {
            $this->zendEventManager->trigger($eventName, null, [$dataType]);
        };

        return $fn;
    }
}
