<?php


namespace SiteTool\Writer;

use SiteTool\Writer;

class StdoutWriter implements Writer
{
  public function write($string, ...$otherStrings)
    {
        echo $string;
        foreach ($otherStrings as $otherString) {
            echo ", ";
            echo $otherString;
        }
        echo PHP_EOL;
    }
}
