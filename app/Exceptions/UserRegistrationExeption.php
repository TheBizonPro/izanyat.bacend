<?php

namespace App\Exceptions;

use Exception;

class UserRegistrationExeption extends Exception
{
    protected array $errors = [];

    /**
     * @param array $errors
     */
    public function __construct(string $message, int $code, array $errors)
    {
        $this->message = $message;
        $this->code = $code;
        $this->errors = $errors;

        parent::__construct($message, $code);
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

}
