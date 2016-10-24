<?php


namespace SiteTool;

class Output
{
    private $count = 0;
    
    public function gettingUrl($fullURL)
    {
        $this->count++;
        
        if ($this->count % 10 == 0) {
            echo "\n";
        }
        echo ".";
        echo "Getting $fullURL \n";
    }
}
