<?php

namespace App\Models;

use App\Models\Definitions\PaymentsSystemsNames;
use App\Models\Interfaces\BankAccount;
use Illuminate\Database\Eloquent\Model;

class TinkoffContractorCard extends Model implements BankAccount
{
    protected $table = 'tinkoff_contractor_cards';

    protected $fillable = [

    ];

    public static function createOne(User $user, array $params): TinkoffContractorCard
    {
        $card = self::create($params);

        UserPaymentMethod::create([
            'payment_type_code' => PaymentsSystemsNames::TINKOFF,
            'user_id' => $user->id
        ]);

        return $card;

    }

    public function tinkoffBankAccount()
    {
        return $this->belongsTo(TinkoffContractorAccount::class);
    }

    public function isBankAccountConfigured(): bool
    {
        return isset($this->card_number);
    }
}
