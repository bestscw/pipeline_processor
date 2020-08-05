<?php

namespace PipelineProcessor\Handler;

use PipelineProcessor\Handler\AbstractHandler;

class OrderHandler extends AbstractHandler
{
    protected function perform($record)
    {
	   echo "Order OK\n";
	   print_r($record);
	   echo "\n";
    }
}
