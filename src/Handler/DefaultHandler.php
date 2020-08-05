<?php

namespace PipelineProcessor\Handler;

use PipelineProcessor\Handler\AbstractHandler;

class DefaultHandler extends AbstractHandler
{
    protected function perform($record)
    {
	   echo "Default Handler\n";
	   print_r($record);
	   echo "\n";
    }
}
