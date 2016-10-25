<?php

namespace SiteTool\StatusWriter;

use SiteTool\StatusWriter;


class StdoutStatusWriter implements StatusWriter
{
    public function write($string)
    {
        echo $string;
        echo PHP_EOL;
    }
}
