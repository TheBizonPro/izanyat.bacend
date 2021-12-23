<?php

namespace App\Models;

use App\Helpers\ArrayHelper;
use App\Models\Definitions\PaymentsSystemsNames;
use App\Services\Telegram\TelegramAdminBotClient;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string|null $email
 * @property int|null $phone
 * @property string|null $phone_code
 * @property int|null $phone_code_sent_at
 * @property int $phone_confirmed
 * @property string|null $password
 * @property string|null $remember_token
 * @property string|null $inn
 * @property string|null $firstname
 * @property string|null $lastname
 * @property string|null $patronymic
 * @property string|null $sex
 * @property string|null $birth_place
 * @property string|null $birth_date
 * @property string|null $passport_series
 * @property string|null $passport_number
 * @property string|null $passport_code
 * @property string|null $passport_issuer
 * @property string|null $passport_issue_date
 * @property string|null $snils
 * @property int|null $is_identified
 * @property int|null $signme_id
 * @property string|null $signme_code
 * @property int|null $taxpayer_registred_as_npd
 * @property int|null $taxpayer_binded_to_platform
 * @property int|null $taxpayer_income_limit_not_exceeded
 * @property int|null $taxpayer_bind_id
 * @property float|null $rating
 * @property string|null $about
 * @property int $is_administrator
 * @property int $is_client
 * @property int $is_selfemployed
 * @property int|null $job_category_id
 * @property int|null $company_id
 * @property string|null $address_region
 * @property string|null $address_city
 * @property string|null $address_street
 * @property string|null $address_house
 * @property string|null $address_building
 * @property string|null $address_flat
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $must_have_task_documents
 * @property-read \App\Models\UserBankAccount|null $bankAccount
 * @property-read \App\Models\Company|null $company
 * @property-read mixed $full_name
 * @property-read mixed $name
 * @property-read mixed $wait_before_repeat_code
 * @property-read \App\Models\JobCategory|null $jobCategory
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\NpdOfflineKeys[] $npdOfflineKeys
 * @property-read int|null $npd_offline_keys_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Sanctum\PersonalAccessToken[] $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory(...$parameters)
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User query()
 * @method static Builder|User whereAbout($value)
 * @method static Builder|User whereAddressBuilding($value)
 * @method static Builder|User whereAddressCity($value)
 * @method static Builder|User whereAddressFlat($value)
 * @method static Builder|User whereAddressHouse($value)
 * @method static Builder|User whereAddressRegion($value)
 * @method static Builder|User whereAddressStreet($value)
 * @method static Builder|User whereBirthDate($value)
 * @method static Builder|User whereBirthPlace($value)
 * @method static Builder|User whereCompanyId($value)
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereFirstname($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereInn($value)
 * @method static Builder|User whereIsAdministrator($value)
 * @method static Builder|User whereIsClient($value)
 * @method static Builder|User whereIsIdentified($value)
 * @method static Builder|User whereIsSelfemployed($value)
 * @method static Builder|User whereJobCategoryId($value)
 * @method static Builder|User whereLastname($value)
 * @method static Builder|User whereMustHaveTaskDocuments($value)
 * @method static Builder|User wherePassportCode($value)
 * @method static Builder|User wherePassportIssueDate($value)
 * @method static Builder|User wherePassportIssuer($value)
 * @method static Builder|User wherePassportNumber($value)
 * @method static Builder|User wherePassportSeries($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User wherePatronymic($value)
 * @method static Builder|User wherePhone($value)
 * @method static Builder|User wherePhoneCode($value)
 * @method static Builder|User wherePhoneCodeSentAt($value)
 * @method static Builder|User wherePhoneConfirmed($value)
 * @method static Builder|User whereRating($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereSex($value)
 * @method static Builder|User whereSignmeCode($value)
 * @method static Builder|User whereSignmeId($value)
 * @method static Builder|User whereSnils($value)
 * @method static Builder|User whereTaxpayerBindId($value)
 * @method static Builder|User whereTaxpayerBindedToPlatform($value)
 * @method static Builder|User whereTaxpayerIncomeLimitNotExceeded($value)
 * @method static Builder|User whereTaxpayerRegistredAsNpd($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * Таблица и настройки индекса
     */
    protected $table = "users";
    protected $primaryKey = "id";
    protected $keyType = "integer";
    public $incrementing = true;
    protected $guarded = ["*"];
    public $timestamps = true;

    public $fillable = [
        'phone',
        'lastname',
        'firstname',
        'patronymic',
        'birth_date',
        'birth_place',
        'sex',
        'email',
        'passport_series',
        'passport_number',
        'passport_issuer',
        'passport_issue_date',
        'passport_code',
        'inn',
        'company_id',
        'is_client',
        'is_identified',
        'is_selfemployed',
        'snils',
        'taxpayer_registred_as_npd',
        'taxpayer_binded_to_platform',
        'taxpayer_income_limit_not_exceeded',
        'taxpayer_bind_id',
        'address_region',
        'address_city',
        'address_street',
        'address_house',
        'address_building',
        'address_flat',
        'job_category_id',
        'must_have_documents',
    ];

    public $hidden = [
        "password",
        "remember_token",
    ];

    public $appends = [];


    /**
     * Загрузчик класса
     */
    protected static function boot()
    {
        parent::boot();
        //self::observe(new \App\Observers\UserObserver);
    }

    public function signMeState()
    {
        return $this->hasOne(SignMeUserState::class);
    }


    /**
     * Валидация модели
     */
    public function validate()
    {
        //
    }

    public function getProjects()
    {

        if ($this->hasPermissionTo('company.admin'))
            return Project::whereCompanyId($this->company_id);

        return $this->projects();
    }


    /**
     * Статический метод форматирования номера телефона
     */
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

    /**
     * Текущая компания пользователя (клиента)
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'user_projects');
    }

    public function hasAccessToProject(int $projectId): bool
    {
        if ($this->can('company.admin')) {
            return true;
        }
        return $this->projects()->find($projectId) !== NULL;
    }

    public function npdOfflineKeys(): HasMany
    {
        return $this->hasMany(NpdOfflineKeys::class);
    }

    public function tinkoffBankAccount()
    {
        return $this->hasOne(TinkoffContractorAccount::class);
    }

    public function getPaymentSystemAccount(PaymentSystem $paymentSystem)
    {
        return match ($paymentSystem->code) {
            PaymentsSystemsNames::MOBI => $this->bankAccount,

            PaymentsSystemsNames::TINKOFF => $this->tinkoffBankAccount,

            default => throw new \Exeption('Неизвестная платёжная система')
        };
    }

    public function paymentMethods()
    {
        return $this->hasMany(UserPaymentMethod::class);
    }

    public function setNewConfirmationCode($code)
    {
        // Новый смс-код
        $this->phone_code = $code;
        $this->phone_code_sent_at = Carbon::now()->timestamp;
        $this->save();

        return $this->phone_code;
    }

    /**
     * @throws \Exception
     */
    public function updateEmployeePersonalData(array $dataToUpdate, array $unsetIfModelHasField = []): bool|int
    {
        if (isset($this->signme_id) or $this->is_identified == 1)
            throw new \Exception('Невозможно персональные данные т.к есть активная привязка к SignMe');

        foreach ($unsetIfModelHasField as $key => $item) {
            if (isset($this->$item))
                unset($dataToUpdate[$key]);
        }

        return $this->update($dataToUpdate);
    }

    /**
     * @param array $dataToUpdate
     * @return bool|int
     * @throws \Exception
     */
    public function updateContractorPersonalInfo(array $dataToUpdate, array $unsetIfModelHasField = []): bool|int
    {
        // если ФИО, при этом смз привязан к мой налог - не даем обновить фио
        if (
            ArrayHelper::atLeastOneKeyExists(['firstname', 'lastname', 'patronymic'], $dataToUpdate)
            and
            $this->taxpayer_binded_to_platform
        ) {
            throw new \Exception('Невозможно изменить ФИО после привязки к Мой Налог');
        }

        if ($this->is_identified) {
            throw new \Exception('Невозможно изменить данные после прохождения идентификации');
        }

        foreach ($unsetIfModelHasField as $key => $item) {
            if (isset($this->$item))
                unset($dataToUpdate[$key]);
        }

        return $this->update($dataToUpdate);
    }

    public function canReceiveConfirmationSMS(): bool
    {
        if (!$this->phone_code_sent_at)
            return true;

        return $this->wait_before_repeat_code <= 0;
    }

    /**
     * Задает оффлайн ключ
     *
     * @param string $hashKey
     * @param int $sequenceNumber
     * @param string $expireTime
     * @return Model
     */
    public function setNpdOfflineKey(string $hashKey, int $sequenceNumber, string $expireTime): Model
    {
        return NpdOfflineKeys::create([
            'user_id' => $this->id,
            'sequence_number' => $sequenceNumber,
            'hash_key' => $hashKey,
            'expire_time' => Carbon::parse($expireTime)->format('d.m.y h:i')
        ]);
    }

    public function jobCategory()
    {
        return $this->hasOne(JobCategory::class, 'id', 'job_category_id');
    }

    public function bankAccount()
    {
        return $this->hasOne(UserBankAccount::class);
    }

    /**
     * Установка номера телефона
     */
    public function setPhoneAttribute($phone)
    {
        $this->attributes['phone'] = self::formatPhone($phone);
    }

    public function isCompanyAdmin()
    {
        return $this->can('company.admin');
    }

    /**
     * Получение дня рождения
     */
    public function getBirthDateAttribute()
    {
        if ($this->attributes['birth_date']) {
            return Carbon::createFromFormat('Y-m-d', $this->attributes['birth_date'])->format('d.m.Y');
        }
    }
    /**
     * Установка дня рождения
     */
    public function setBirthDateAttribute($birth_date)
    {
        $this->attributes['birth_date'] = Carbon::createFromFormat('d.m.Y', $birth_date)->format('Y-m-d');
    }

    /**
     * Получение Дата выдачи паспорта
     */
    public function getPassportIssueDateAttribute()
    {
        if ($this->attributes['passport_issue_date']) {
            return Carbon::createFromFormat('Y-m-d', $this->attributes['passport_issue_date'])->format('d.m.Y');
        }
    }

    /**
     * Установка Дата выдачи паспорта
     */
    public function setPassportIssueDateAttribute($birth_date)
    {
        $this->attributes['passport_issue_date'] = Carbon::createFromFormat('d.m.Y', $birth_date)->format('Y-m-d');
    }


    /**
     * Время ожидания перед повторением отправки кода
     */
    public function getWaitBeforeRepeatCodeAttribute()
    {
        return $this->getWaitBeforeRepeatCode();
    }

    /**
     * Время ожидания перед повторением отправки кода
     */
    public function getWaitBeforeRepeatCode()
    {
        if ($this->phone_code_sent_at != null) {
            $whenYouCanSend = Carbon::parse($this->phone_code_sent_at)
                ->addSeconds(intval(env('SMS_CODE_WAIT_BEFORE_REPEAT')));
            $now = Carbon::now();
            if ($now < $whenYouCanSend) {
                return $whenYouCanSend->diffInSeconds($now);
            }
        }
        return 0;
    }


    /**
     * Фамилия И. О.
     */
    public function getNameAttribute()
    {
        $lastname = $this->attributes['lastname'];
        $firstname_letter = mb_substr($this->attributes['firstname'], 0, 1);
        $patronymic_letter = mb_substr($this->attributes['patronymic'], 0, 1);
        $name = $lastname . ' ';
        $name .= ($firstname_letter != '') ? $firstname_letter . '. ' : '?. ';
        $name .= ($patronymic_letter != '') ? $patronymic_letter . '.' : '?.';
        return $name;
    }


    /**
     * Фамилия Имя Отчество
     */
    public function getFullNameAttribute()
    {
        return trim($this->lastname . ' ' . $this->firstname . ' ' . $this->patronymic);
    }


    /**
     * Получение свойств для SignMe
     */
    public function forSignMe(string $field)
    {
        switch ($field) {
            case 'name':
                return $this->firstname;
            case 'surname':
                return $this->lastname;
            case 'lastname':
                return $this->patronymic;
            case 'bdate':
                return $this->attributes['birth_date'];
            case 'pdate':
                return $this->attributes['passport_issue_date'];
            case 'gender':
                return mb_strtoupper($this->sex);
            case 'country':
                return 'ru';
            case 'region':
                return $this->address_region;
            case 'city':
                return $this->address_city;
            case 'ps':
                return $this->passport_series;
            case 'pn':
                return $this->passport_number;
            case 'issued':
                return $this->passport_issuer;
            case 'pcode':
                return $this->passport_code;
            case 'snils':
                return preg_replace("/\D/", "", trim($this->snils));
            case 'phone':
                return '+' . $this->phone;
            case 'email':
                return $this->email;
            case 'inn':
                return $this->inn;
            case 'street':
                return $this->address_street;
            case 'house':
                return $this->address_house;
            case 'building':
                return $this->address_building;
            case 'room':
                return $this->address_flat;
            case 'external':
                return $this->id;
            default:
                return null;
        }
    }


    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
