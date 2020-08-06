<?php

namespace PipelineProcessor\Handler;

use PipelineProcessor\Handler\AbstractHandler;

class PayHandler extends AbstractHandler
{
    protected function perform($record)
    {
        $this->getLogger()->info("",$record);
    }
}
