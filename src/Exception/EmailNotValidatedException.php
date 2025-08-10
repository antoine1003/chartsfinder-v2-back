<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class EmailNotValidatedException extends \Exception
{
    protected $message = 'emailNotValidated'; // Message to be returned in the response
    protected $code = Response::HTTP_UNAUTHORIZED; // HTTP Unauthorized status code

    public function __construct()
    {
        parent::__construct($this->message, $this->code);
    }
}
