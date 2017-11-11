<?php

declare(strict_types=1);

namespace SiteTool;

interface Validator
{
    public function validate(string $w3cResultHtml);
}
