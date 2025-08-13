<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class UsernameAlreadyExistsException extends \Exception
{
    protected $message = 'usernameAlreadyExists'; // Message to be returned in the response
    protected $code = Response::HTTP_CONFLICT; // HTTP Conflict status code

    public function __construct()
    {
        parent::__construct($this->message, $this->code);
    }
}
