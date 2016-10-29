<?php


namespace SiteTool;


interface MigrationResultWriter
{
    public function write($string, ...$otherStrings);
}
