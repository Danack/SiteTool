<?php


namespace SiteTool\Writer;

use SiteTool\Writer;

class StderrWriter implements Writer
{
    public function write($string, ...$otherStrings)
    {
        fwrite(STDERR, $string);
        foreach ($otherStrings as $otherString) {
            fwrite(STDERR, ", ");
            fwrite(STDERR, $otherString);
        }
        fwrite(STDERR, PHP_EOL);
    }
}
