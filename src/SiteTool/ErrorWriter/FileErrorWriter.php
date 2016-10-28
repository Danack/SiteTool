<?php

namespace SiteTool\ErrorWriter;

use SiteTool\ErrorWriter;
use SiteTool\SiteToolException;




class FileErrorWriter implements ErrorWriter
{
    private $fileHandle;
    
    public function __construct($filename)
    {
        $this->fileHandle = fopen($filename, "w");
        if ($this->fileHandle === false) {
            throw new SiteToolException("Failed to open $filename for writing.");
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
        $line = $string;
        if (count($otherStrings) !== 0) {
            $line .= ", ";
        }

        $line .= implode(", ", $otherStrings);
        $line .= "\n";

        fwrite($this->fileHandle, $line);
    }
}
