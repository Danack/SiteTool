<?php

declare(strict_types=1);

namespace SiteTool;

interface ProcessSourceList
{
    public function getProcessList();

    public function getSetupFunction();

    public function endOfProcessing();
}