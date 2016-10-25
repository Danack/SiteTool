<?php

namespace SiteTool\StatusWriter;

use SiteTool\StatusWriter;


class NullStatusWriter implements StatusWriter
{
    public function write($string)
    {
    }
}
