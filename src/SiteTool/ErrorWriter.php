<?php


namespace SiteTool;


interface ErrorWriter
{
    public function write($string, ...$otherStrings);
}
