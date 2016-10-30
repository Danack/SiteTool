<?php


namespace SiteTool\Writer;

use SiteTool\SiteToolException;
use SiteTool\Writer;

class FileWriter implements Writer
{
    private $fileHandle = null;
    private $filename;
    
    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    private function init()
    {
        if ($this->fileHandle) {
            return;
        }
        $this->fileHandle = fopen($this->filename, "w");
        if ($this->fileHandle === false) {
            throw new SiteToolException("Failed to open " . $this->filename . " for writing.");
        }
    }
    
    public function __destruct()
    {
        if ($this->fileHandle) {
            fclose($this->fileHandle);
        }
    }

    public function write($string, ...$otherStrings)
    {
        $this->init();
        $line = $string;
        if (count($otherStrings) !== 0) {
            $line .= ", ";
        }

        $line .= implode(", ", $otherStrings);
        $line .= "\n";

        fwrite($this->fileHandle, $line);
    }
}
