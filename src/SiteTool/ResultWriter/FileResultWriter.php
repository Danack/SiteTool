<?php

namespace SiteTool\ResultWriter;

use SiteTool\ResultWriter;
use SiteTool\SiteToolException;




class FileResultWriter implements ResultWriter
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

    public function write(
        $url,
        $status,
        $referrer,
        $body
    ) {
        $string = sprintf(
            "%s, %s, %s\n",
            $status,
            $url,
            $referrer//,
            //$body
        );

        fwrite($this->fileHandle, $string);
    }
}
