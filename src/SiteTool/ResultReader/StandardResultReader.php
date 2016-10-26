<?php

namespace SiteTool\ResultReader;

use SiteTool\ResultReader;
use SiteTool\SiteToolException;
use SiteTool\Result;

class StandardResultReader implements ResultReader
{
    private $fileHandle;
    
    public function __construct($filename)
    {
        $this->fileHandle = fopen($filename, "r");
        if ($this->fileHandle === false) {
            throw new SiteToolException("Failed to open $filename for writing.");
        }
    }

    /**
     * @return \SiteTool\Result[]
     */
    public function readAll()
    {
        $results = [];
        while(($line = fgets($this->fileHandle)) !== false) {
            $parts = explode(',', $line);
            $results[] = new Result(
                trim($parts[0]),
                trim($parts[1]),
                trim($parts[2])
            );
        }
        fclose($this->fileHandle);

        return $results;
    }
    
}
