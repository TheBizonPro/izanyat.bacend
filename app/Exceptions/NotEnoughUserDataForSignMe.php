<?php

namespace App\Exceptions;

class NotEnoughUserDataForSignMe extends \Exception
{
    protected array $fields = [];

    /**
     * @param array $fields
     */
    public function __construct(array $fields)
    {
        $this->fields = $fields;
        $this->message = 'Недостаточно данных для регистрации в SignMe';
    }


    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param array $fields
     */
    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }

}
