<?php

namespace App\Services\Adapters;

use Closure;
use Laravel\SerializableClosure\SerializableClosure;
use Log;

class CallbackSerialize
{
    public static function call(Closure $callback, mixed $arg): string
    {
        return serialize(new SerializableClosure($callback));
    }
}
