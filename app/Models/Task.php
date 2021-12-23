<?php

namespace App\Models;

use App\Models\Interfaces\BankAccount;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Task
 *
 * @property int $id
 * @property int $project_id
 * @property string $name
 * @property string $status
 * @property string|null $description
 * @property string|null $address
 * @property int|null $user_id
 * @property int $job_category_id
 * @property string $date_from
 * @property string $date_till
 * @property float $sum
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $act_id
 * @property int|null $order_id
 * @property-read \App\Models\Document|null $act
 * @property-read mixed $created_date
 * @property-read \App\Models\JobCategory|null $jobCategory
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Offer[] $offers
 * @property-read int|null $offers_count
 * @property-read \App\Models\Document|null $order
 * @property-read \App\Models\Project $project
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Task newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Task newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Task query()
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereActId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereDateFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereDateTill($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereJobCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereSum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereUserId($value)
 * @mixin \Eloquent
 */
class Task extends Model
{
    use HasFactory;

    /**
     * Таблица и настройки индекса
     */
    protected $table = "tasks";
    protected $primaryKey = "id";
    protected $keyType = "integer";
    public $incrementing = true;
    protected $guarded = ["*"];
    public $timestamps = true;

    public $fillable = [
        'name',
        'project_id',
        'description',
        'address',
        'job_category_id',
        'date_from',
        'date_till',
        'sum',
        'status',
        'contractor_payment_method_id',
        'company_payment_type',
        'act_id',
        'order_id',
        'company_id',
        'is_sum_confirmed',
        'payments_systems_id',
    ];

    public $hidden = [];

    public $appends = [];


    /**
     * Загрузчик класса
     */
    protected static function boot()
    {
        parent::boot();
        //self::observe(new \App\Observers\TaskObserver);
    }

    /**
     * Исполнитель
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function company()
    {
        return $this->project->company;
    }

    public function getPaymentAccount(): BankAccount
    {
        $paymentType = $this->paymentType;

//        match ($paymentType->payment_type_code) {
//
//        };

        return $this->paymentType;
    }

    public function paymentType()
    {
        return $this->hasOne(UserPaymentMethod::class, 'id', 'contractor_payment_method_id');
    }

    /**
     * Проект
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }


    /**
     * Предложения работы
     */
    public function offers()
    {
        return $this->hasMany(Offer::class, 'task_id', 'id');
    }

    /**
     * Заказ
     */
    public function order()
    {
        return $this->hasOne(Document::class, 'id', 'order_id');
    }

    /**
     * вид работ
     */
    public function jobCategory()
    {
        return $this->hasOne(JobCategory::class, 'id', 'job_category_id');
    }

    public function act()
    {
        return $this->hasOne(Document::class, 'id', 'act_id');
    }

    public function hasDocumentsToBeDone(): bool
    {
        return $this->order !== null;
    }

    /**
     *
     */
    public function getCreatedDateAttribute()
    {
        return Carbon::parse($this->created_at)->format('d.m.Y');
    }

    /**
     *
     */
    public function setDateFromAttribute($date)
    {
        $this->attributes['date_from'] = Carbon::parse($date)->format('Y-m-d');
    }

    /**
     *
     */
    public function setDateTillAttribute($date)
    {
        $this->attributes['date_till'] = Carbon::parse($date)->format('Y-m-d');
    }
}
