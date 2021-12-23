<?php

namespace App\Models;

use App\Models\Interfaces\BankAccount;
use Illuminate\Database\Eloquent\Model;

class UserPaymentMethod extends Model
{
    protected $table = 'user_payment_methods';

    protected $fillable = [
        'payment_type_code',
        'payment_method_id',
        'user_id'
    ];

    public function toTinkoffCard(): TinkoffContractorCard
    {
        return TinkoffContractorCard::findOrFail($this->payment_method_id);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
