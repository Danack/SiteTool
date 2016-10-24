<?php

namespace SiteTool;

use Danack\Console\Application;
use Danack\Console\Command\Command;
use Danack\Console\Input\InputArgument;

class ConsoleApplication extends Application
{
    /**
     * Creates a console application with all of the commands attached.
     * @return ConsoleApplication
     */
    public function __construct()
    {
        parent::__construct("ImagickDemos", "1.0.0");

        $statsCommand = new Command('hello:world', 'SiteTool\HelloWorld::run');
        $statsCommand->setDescription("Hello world test.");
        $this->add($statsCommand);
        
        $statsCommand = new Command('site:crawl', 'SiteTool\Crawler::run');
        $statsCommand->setDescription("Crawls a site");
        $this->add($statsCommand);
    

//        $envWriteCommand = new Command('genEnvSettings', 'ImagickDemo\Config\EnvConfWriter::writeEnvFile');
//        $envWriteCommand->setDescription("Write an env setting bash script.");
//        $envWriteCommand->addArgument('env', InputArgument::REQUIRED, 'Which environment the settings should be generated for.');
//        $envWriteCommand->addArgument('filename', InputArgument::REQUIRED, 'The file name that the env settings should be written to.');
//        $this->add($envWriteCommand);
//        
//    
//        $clearRedisCommand = new Command('clearRedis', 'ImagickDemo\Queue\ImagickTaskQueue::clearStatusQueue');
//        $clearRedisCommand->setDescription("Clear the imagick task queue.");
//        $this->add($clearRedisCommand);
    }

//    public static function getDeployCommand()
//    {
//        $deployCommand = new Command('deploy', ['ServerContainer\Deployer\Deployer', 'run']);
//        $deployCommand->setDescription('Deploy an application.');
//        $deployCommand->addArgument(
//            'application',
//            // This is optional so as to allow use to give the error message, instead
//            // of the console app providing a rubbish one.
//            InputArgument::OPTIONAL,
//            "Which application should be deployed."
//        );
//        
//        return $deployCommand;
//    }
}
