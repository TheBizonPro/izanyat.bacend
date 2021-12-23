<?php

namespace App\Models;

use App\Models\Definitions\PaymentsSystemsNames;
use Illuminate\Database\Eloquent\Model;

class PaymentSystem extends Model
{
    public static PaymentsSystemsNames $paymentsSystemsNames;
    protected $table = 'payments_systems';
    protected $primaryKey = 'id';

    protected $fillable = [
        'display_name',
        'code',
    ];

    public function isTinkoff()
    {
        return $this->code = self::$paymentsSystemsNames::TINKOFF;
    }

    public function isMobi()
    {
        return $this->code = self::$paymentsSystemsNames::MOBI;
    }


}
