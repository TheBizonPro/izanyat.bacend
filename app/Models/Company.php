<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Company
 *
 * @property int $id
 * @property int|null $signer_user_id
 * @property float $balance
 * @property string $name
 * @property string|null $full_name
 * @property string|null $address_region
 * @property string|null $address_city
 * @property string|null $legal_address
 * @property string|null $fact_address
 * @property string|null $inn
 * @property string|null $ogrn
 * @property string|null $okpo
 * @property string|null $kpp
 * @property string|null $email
 * @property string|null $about
 * @property int|null $phone
 * @property int|null $signme_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CompanyBankAccount|null $bankAccount
 * @property-read \App\Models\User|null $signerUser
 * @method static \Illuminate\Database\Eloquent\Builder|Company newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Company newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Company query()
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereAbout($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereAddressCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereAddressRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereFactAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereInn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereKpp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereLegalAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereOgrn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereOkpo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereSignerUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereSignmeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Company extends Model
{
    use HasFactory;

    /**
     * Таблица и настройки индекса
     */
    protected $table = "companies";
    protected $primaryKey = "id";
    protected $keyType = "integer";
    public $incrementing = true;
    protected $guarded = ["*"];
    public $timestamps = true;

    public $fillable = [
        'full_name',
        'balance',
        'name',
        'address_region',
        'address_city',
        'legal_address',
        'fact_address',
        'inn',
        'ogrn',
        'okpo',
        'kpp',
        'phone',
        'email',
    ];

    public $hidden = [];

    public $appends = [];


    /**
     * Загрузчик класса
     */
    protected static function boot()
    {
        parent::boot();
        //self::observe(new \App\Observers\CompanyObserver);
    }

    public static function findByInn(string $inn)
    {
        return (new static)::where('inn', $inn)->first();
    }

    public function users()
    {
        return $this->hasMany(User::class, 'company_id', 'id');
    }

    public function signerUser()
    {
        return $this->hasOne(User::class, 'id', 'signer_user_id');
    }

    public function bankAccount()
    {
        return $this->hasOne(CompanyBankAccount::class);
    }


    public static function formatPhone($phone)
    {
        $phone = preg_replace("/\D/", "", trim($phone));
        if (strlen($phone) == 10) {
            $phone = "7" . $phone;
        } else if (strlen($phone) == 11 and mb_substr($phone, 0, 1) === "8") {
            $phone = "7" . mb_substr($phone, 1);
        } else if (strlen($phone) == 11 and mb_substr($phone, 0, 1) === "7") {
            $phone = "7" . mb_substr($phone, 1); // its okey
        } else {
            return false;
        }
        return $phone;
    }


    public function setPhoneAttribute($phone)
    {
        $this->attributes['phone'] = self::formatPhone($phone);
    }



    /**
     * Получение свойств для SignMe
     */
    public function forSignMe(string $field)
    {
        switch ($field) {
            case 'cname':
                return $this->name;
            case 'cemail':
                return $this->email;
            case 'cphone':
                return "+" . $this->phone;
            case 'ccountry':
                return 'ru';
            case 'cregion':
                return $this->address_region;
            case 'ccity':
                return $this->address_city;
            case 'caddr':
                return $this->legal_address;
            case 'cfaddr':
                return $this->fact_address;
            case 'cinn':
                return $this->inn;
            case 'cogrn':
                return $this->ogrn;
            default:
                return null;
        }
    }
}
