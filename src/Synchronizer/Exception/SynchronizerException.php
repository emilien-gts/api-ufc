<?php

namespace App\Synchronizer\Exception;

use Symfony\Component\HttpFoundation\Response;

class SynchronizerException extends \Exception
{
    public function __construct(string $message = '', int $code = Response::HTTP_INTERNAL_SERVER_ERROR)
    {
        parent::__construct($message, $code);
    }
}
