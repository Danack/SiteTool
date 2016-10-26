<?php

namespace SiteTool;

interface ResultReader
{
     /**
     * @return \SiteTool\Result[]
     */
    public function readAll();
}
