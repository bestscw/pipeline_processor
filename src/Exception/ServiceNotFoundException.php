<?php

namespace PipelineProcessor\Exception;

use Exception;
use \Psr\Container\NotFoundExceptionInterface;

class ServiceNotFoundException extends Exception implements NotFoundExceptionInterface
{

}