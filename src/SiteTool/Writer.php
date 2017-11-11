<?php

namespace SiteTool;

interface Writer
{
    public function write($string, ...$otherStrings);
}
