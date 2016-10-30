<?php


namespace SiteTool\Writer;

use SiteTool\Writer;

interface OutputWriter
{
    const PROGRESS          = 0x1;
    const CRAWL_RESULT      = 0x2;
    const ERROR             = 0x4;
    const MIGRATION_RESULT  = 0x8;
    
    public function write($type, $string, ...$otherStrings);
}
