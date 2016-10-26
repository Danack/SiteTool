<?php

namespace SiteTool\ResultWriter;

class FileResultWriterFactory
{
    public function create($name)
    {
        return new FileResultWriter($name);
    }
}
