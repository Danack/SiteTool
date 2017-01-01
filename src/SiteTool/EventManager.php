<?php


namespace SiteTool;

interface EventManager
{
    public function attachEvent($dataType, callable $listener, $listenerName);
    
    public function createTrigger($eventName, $callerName);
}
