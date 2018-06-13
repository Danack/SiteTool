<?php

declare(strict_types=1);

namespace SiteTool;

interface ProcessSourceList
{
    public function getEventProcessors();

    public function getSetupFunction();

    public function endOfProcessing();
}
