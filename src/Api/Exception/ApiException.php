<?php

namespace App\Api\Exception;

use Symfony\Component\HttpFoundation\Response;

class ApiException extends \Exception
{
    public function __construct(string $message = '', int $code = Response::HTTP_INTERNAL_SERVER_ERROR)
    {
        parent::__construct($message, $code);
    }
}
