<?php


namespace SiteTool;

use Zend\EventManager\EventManager;

class DebuggingEventManager extends EventManager
{
    
    public function trigger($eventName, $target = null, $argv = [])
    {
        echo "event: $eventName\n";
        parent::trigger($eventName, $target, $argv);
    }
    
    public function attach($eventName, callable $listener, $priority = 1)
    {
        echo "attached: $eventName\n";
        //var_dump($listener);
        parent::attach($eventName, $listener, $priority);
    }
    
}
