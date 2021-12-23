<?php

namespace App\Models;

use App\Models\Interfaces\BankAccount;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\UserBankAccount
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $card_number
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|UserBankAccount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserBankAccount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserBankAccount query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserBankAccount whereCardNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBankAccount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBankAccount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBankAccount whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBankAccount whereUserId($value)
 * @mixin \Eloquent
 */
class UserBankAccount extends Model implements BankAccount
{
    protected $table = 'users_bank_accounts';
    protected $primaryKey = 'id';

    /**
     * Исполнитель
     */
    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function hasBankCard(): bool
    {
        return $this->card_number !== NULL;
    }

    public function hiddenCard(){
        $first = substr($this->card_number,0,4);
        $last = substr($this->card_number,strlen($this->card_number)-4,4);
        return "$first **** **** $last";
    }

    protected $fillable = [
        'user_id',
        'card_number',
    ];

    public function isBankAccountConfigured(): bool
    {
        return isset($this->card_number);
    }
}
