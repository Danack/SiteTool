<?php


namespace SiteTool;


interface ResultWriter
{
    public function write($string, ...$otherStrings);
}
