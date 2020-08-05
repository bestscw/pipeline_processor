<?php

namespace PipelineProcessor\Handler;

/**
 * Job处理接口
 *
 * Interface HandlerInterface
 * @package PipelineProcessor\Handler
 */
interface HandlerInterface
{
    public function handle($record);
}
