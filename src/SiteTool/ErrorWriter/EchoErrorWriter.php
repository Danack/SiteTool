<?php

namespace SiteTool\ErrorWriter;

use SiteTool\ErrorWriter;


class EchoErrorWriter  implements ErrorWriter
{
    public function write($string, ...$otherStrings)
    {
        echo $string;
        foreach ($otherStrings as $otherString) {
            echo ", ";
            echo $otherString;
        }
        echo "\n";
    }
}

