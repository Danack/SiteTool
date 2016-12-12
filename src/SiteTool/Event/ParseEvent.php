<?php


namespace SiteTool\Event;

use SiteTool\UrlToCheck;

interface ParseEvent
{
    
    function foundUrlEvent($href, UrlToCheck $urlToCheck);

}
