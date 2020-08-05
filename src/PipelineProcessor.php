<?php 
namespace PipelineProcessor;

use PipelineProcessor\Handler\HandlerInterface;
use PipelineProcessor\Handler\DefaultHandler;
use Psr\Log\InvalidArgumentException;


class PipelineProcessor{
    
    /**
     * Detailed debug information
     */
    const DEBUG = 100;
    
    /**
     * Interesting events
     *
     * Examples: User logs in, SQL logs.
     */
    const INFO = 200;
    
    /**
     * Uncommon events
     */
    const NOTICE = 250;
    
    /**
     * Exceptional occurrences that are not errors
     *
     * Examples: Use of deprecated APIs, poor use of an API,
     * undesirable things that are not necessarily wrong.
     */
    const WARNING = 300;
    
    /**
     * Runtime errors
     */
    const ERROR = 400;
    
    /**
     * Critical conditions
     *
     * Example: Application component unavailable, unexpected exception.
     */
    const CRITICAL = 500;
    
    /**
     * Action must be taken immediately
     *
     * Example: Entire website down, database unavailable, etc.
     * This should trigger the SMS alerts and wake you up.
     */
    const ALERT = 550;
    
    /**
     * Urgent alert.
     */
    const EMERGENCY = 600;
    
    /**
     * The handler stack
     *
     * @var HandlerInterface[]
     */
    protected $handlers;
    
    /**
     * Processors that will process all log records
     *
     * To process records of a single handler instead, add the processor on that specific handler
     *
     * @var callable[]
     */
    protected $processors;
    
    /**
     * @var callable
     */
    protected $exceptionHandler;
    
    /**
     * @var string
     */
    protected $name;
    
    /**
     * Logging levels from syslog protocol defined in RFC 5424
     *
     * @var array $levels Logging levels
     */
    protected static $levels = array(
        self::DEBUG     => 'DEBUG',
        self::INFO      => 'INFO',
        self::NOTICE    => 'NOTICE',
        self::WARNING   => 'WARNING',
        self::ERROR     => 'ERROR',
        self::CRITICAL  => 'CRITICAL',
        self::ALERT     => 'ALERT',
        self::EMERGENCY => 'EMERGENCY',
    );
    
    /**
     * @param string             $name       The logging channel
     * @param HandlerInterface[] $handlers   Optional stack of handlers, the first one in the array is called first, etc.
     * @param callable[]         $processors Optional array of processors
     */
    public function __construct($name, array $handlers = array(), array $processors = array())
    {
        $this->name = $name;
        $this->setHandlers($handlers);
        $this->processors = $processors;
    }
    
    /**
     * 返回管道名称
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Return a new cloned instance with the name changed
     *
     * @return static
     */
    public function withName($name)
    {
        $new = clone $this;
        $new->name = $name;
        
        return $new;
    }
    /**
     * Pushes a handler on to the stack.
     *
     * @param  HandlerInterface $handler
     * @return $this
     */
    public function pushHandler(HandlerInterface $handler)
    {
        array_unshift($this->handlers, $handler);
        
        return $this;
    }
    
    /**
     * Pops a handler from the stack
     *
     * @return HandlerInterface
     */
    public function popHandler()
    {
        if (!$this->handlers) {
            throw new \LogicException('You tried to pop from an empty handler stack.');
        }
        
        return array_shift($this->handlers);
    }
    
    /**
     * Set handlers, replacing all existing ones.
     *
     * If a map is passed, keys will be ignored.
     *
     * @param  HandlerInterface[] $handlers
     * @return $this
     */
    public function setHandlers(array $handlers)
    {
        $this->handlers = array();
        foreach (array_reverse($handlers) as $handler) {
            $this->pushHandler($handler);
        }
        
        return $this;
    }
    
    /**
     * @return HandlerInterface[]
     */
    public function getHandlers()
    {
        return $this->handlers;
    }
    
    /**
     * Gets the name of the logging level.
     *
     * @param  int    $level
     * @return string
     */
    public static function getLevelName($level)
    {
        if (!isset(static::$levels[$level])) {
            throw new InvalidArgumentException('Level "'.$level.'" is not defined, use one of: '.implode(', ', array_keys(static::$levels)));
        }
        
        return static::$levels[$level];
    }
    
    /**
     * Adds a log record.
     *
     * @param  int     $level   The logging level
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return bool Whether the record has been processed
     */
    public function addRecord($level, $message, array $context = array())
    {
        if (!$this->handlers) {
            $this->pushHandler(new DefaultHandler());
        }
        
        $levelName = static::getLevelName($level);
        
        $record = array(
            'message' => (string) $message,
            'context' => $context,
            'level' => $level,
            'level_name' => $levelName,
            'channel' => $this->name,
            'extra' => array(),
        );
        
        try {
            while ($handler = current($this->handlers)) {
                if (true === $handler->handle($record)) {
                    break;
                }
                
                next($this->handlers);
            }
        } catch (\Exception $e) {
            $this->handleException($e, $record);
        }
        
        return true;
    }
    
    /**
     * Adds a log record at the INFO level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string $message The log message
     * @param  array  $context The log context
     * @return bool   Whether the record has been processed
     */
    public function info($message, array $context = array())
    {
        return $this->addRecord(static::INFO, $message, $context);
    }
    
    /**
     * Adds a log record at the NOTICE level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string $message The log message
     * @param  array  $context The log context
     * @return bool   Whether the record has been processed
     */
    public function notice($message, array $context = array())
    {
        return $this->addRecord(static::NOTICE, $message, $context);
    }
    
    /**
     * Set a custom exception handler
     *
     * @param  callable $callback
     * @return $this
     */
    public function setExceptionHandler($callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('Exception handler must be valid callable (callback or object with an __invoke method), '.var_export($callback, true).' given');
        }
        $this->exceptionHandler = $callback;
        
        return $this;
    }
    
    /**
     * @return callable
     */
    public function getExceptionHandler()
    {
        return $this->exceptionHandler;
    }
    
    /**
     * Delegates exception management to the custom exception handler,
     * or throws the exception if no custom handler is set.
     */
    protected function handleException(\Exception $e, array $record)
    {
        if (!$this->exceptionHandler) {
            throw $e;
        }
        
        call_user_func($this->exceptionHandler, $e, $record);
    }
    
    /**
     * Ends a log cycle and frees all resources used by handlers.
     *
     * Closing a Handler means flushing all buffers and freeing any open resources/handles.
     * Handlers that have been closed should be able to accept log records again and re-open
     * themselves on demand, but this may not always be possible depending on implementation.
     *
     * This is useful at the end of a request and will be called automatically on every handler
     * when they get destructed.
     */
    public function close()
    {
        foreach ($this->handlers as $handler) {
            if (method_exists($handler, 'close')) {
                $handler->close();
            }
        }
    }
}
?>