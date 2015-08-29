<?php

namespace L5SimpleFM\Exceptions;

class BaseException extends \Exception
{
    protected $commandResult;

    public function __construct($message, $code = 0, $commandResult, \Exception $previous = null)
    {
        $this->commandResult = $commandResult;
        parent::__construct($message, $code, $previous);
    }

    public function getCommandResult()
    {
        return $this->commandResult;
    }
}
