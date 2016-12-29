<?php


namespace SiteTool;

interface EventManager
{
    public function attach($eventName, callable $listener, $listenerName);
    
    public function createTrigger($eventName, $callerName);
}
