<?php

namespace PipelineProcessor\Handler;

use PipelineProcessor\Handler\AbstractHandler;

class PayHandler extends AbstractHandler
{
    protected function perform($record)
    {
	   echo "Pay OK\n";
	   print_r($record);
	   echo "\n";
    }
}
