<?php

namespace SiteTool\ErrorWriter;

use SiteTool\ErrorWriter;


class NullErrorWriter  implements ErrorWriter
{
    public function write($string, ...$otherStrings)
    {
        
    }
}

