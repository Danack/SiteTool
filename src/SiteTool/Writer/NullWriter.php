<?php

namespace SiteTool\Writer;

use SiteTool\Writer;

class NullWriter implements Writer
{
    public function write($string, ...$otherStrings)
    {
    }
}
