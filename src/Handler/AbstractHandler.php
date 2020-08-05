<?php

namespace PipelineProcessor\Handler;

require_once 'HandlerInterface.php';

use Exception;
use PipelineProcessor\Handler\HandlerInterface;

/**
 * Job处理抽象类
 *
 * Class AbstractHandler
 * @package PipelineProcessor\Handler
 */
abstract class AbstractHandler implements HandlerInterface
{

    /**
     * @var string Job唯一标识
     */
    protected $id;

    /**
     * @var array
     */
    protected $body;

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param array $body
     */
    public function setBody(array $body)
    {
        $this->body = $body;
    }

    public function handle($record)
    {
        $this->setUp();

        try {
            $this->perform($record);
        } catch (Exception $exception) {
        }

        $this->tearDown();
    }
    
    /**
     * Closes the handler.
     *
     * This will be called automatically when the object is destroyed
     */
    public function close(){ }

    protected function setUp() { }

    protected function tearDown() { }

    abstract protected function perform($record);
}
