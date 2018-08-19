<?php

namespace RouterOS\Exceptions;

class Exception extends \Exception
{
    public function __construct(string $message = '', int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        error_log(
            'Uncaught Error: ' . $this->getMessage() . ' in ' . $this->getFile() . ':' . $this->getLine() . "\n"
            . "Stack trace:\n" . $this->getTraceAsString() . "\n"
            . '  thrown in ' . $this->getFile() . ' on line ' . $this->getLine()
        );
    }
}
