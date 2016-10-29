<?php

namespace SiteTool\MigrationResultWriter;

use SiteTool\MigrationResultWriter;
use SiteTool\SiteToolException;

class FileMigrationResultWriter implements MigrationResultWriter
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
