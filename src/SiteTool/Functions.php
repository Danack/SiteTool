<?php

declare(strict_types=1);

namespace SiteTool;

use Danack\Console\Application;
use Danack\Console\Command\Command;
use Danack\Console\Input\InputArgument;

class Functions
{
    public static function createApplication()
    {
        $application = new Application("SiteTool", "1.0.0");
        $goCommand = new Command('site:go', 'SiteTool\Command\RunTheThings::run');
        $goCommand->setDescription("GoGoGo");

        $goCommand->addArgument(
            'processSource',
            InputArgument::REQUIRED,
            'The class name that contains the list of items to process'
        );
        $application->add($goCommand);


        $graphCommand = new Command('site:graph', '\SiteTool\Command\GraphTheThings::run');
        $graphCommand->setDescription("GraphTheThings");
        $graphCommand->addArgument(
            'processSource',
            InputArgument::REQUIRED,
            'The class name that contains the list of items to process'
        );
        $application->add($graphCommand);

        return $application;
    }

    public static function normalizeEventName($eventName)
    {
        $lastSlashPos = strrpos($eventName, '\\');
        if ($lastSlashPos !== false) {
            return substr($eventName, $lastSlashPos + 1);
        }
        return $eventName;
    }
}
