<?php
namespace PipelineProcessor\Container;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;


/**
 *
 * @see Mongo
 */
trait LoggerTrait
{
 
    /**
     * 日志处理
     * @param string $channel
     * @param string $log_path
     * @return NULL|\Monolog\Logger
     */
    public function getLogger($channel="test",$log_path = "/tmp/test.log"){
        static $logger = null;
        if($logger == null){
            // create a log channel
            $logger = new Logger($channel);
            $logger->pushHandler(new StreamHandler($log_path, Logger::INFO));
        }
        return $logger;
    }
}