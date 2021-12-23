<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TinkoffContractorAccount extends Model
{
    protected $table = 'tinkoff_contractor_accounts';

    protected $fillable = [
        'tinkoff_customer_key'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
