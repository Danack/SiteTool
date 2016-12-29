<?php

namespace SiteTool\EventManager;

use SiteTool\EventManager;
use SiteTool\GraphVizTest;
use Zend\EventManager\EventManager as ZendEventManager;




class BlahEventManager implements EventManager
{
    /** @var ZendEventManager  */
    private $zendEventManager;
    
    /** @var GraphVizTest  */
    private $graphVizTest;
    
    public function __construct(
        ZendEventManager $zendEventManager,
        GraphVizTest $graphVizTest
    ) {
        $this->zendEventManager = $zendEventManager;
        $this->graphVizTest = $graphVizTest;
    }

    public function attach($eventName, callable $listener, $listenerName)
    {
        $this->zendEventManager->attach($eventName, $listener);
        $this->graphVizTest->addEventListener($eventName, $listenerName);
    }

    public function createTrigger($eventName, $callerName)
    {
        
        
        
        $this->graphVizTest->addEventTrigger($eventName, $callerName);
        $fn = function ($params) use ($eventName) {
            $this->zendEventManager->trigger($eventName, null, $params);
        };

        return $fn;
    }
}
