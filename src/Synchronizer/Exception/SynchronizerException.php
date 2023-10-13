<?php

namespace App\Synchronizer\Exception;

class SynchronizerException extends \Exception
{
    public function __construct(string $message = '', int $code = 0)
    {
        parent::__construct($message, $code);
    }
}
