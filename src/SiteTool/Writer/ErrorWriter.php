<?php


namespace SiteTool\Writer;

use SiteTool\Writer;

class ErrorWriter
{
    /** @var Writer */
    private $writer;
    
    public function __construct(Writer $writer)
    {
        $this->writer = $writer;
    }

    public function write($string, ...$otherStrings)
    {
        $this->writer->write($string, ...$otherStrings);
    }
}
