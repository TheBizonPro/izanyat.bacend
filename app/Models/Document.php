<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


/**
 * App\Models\Document
 *
 * @property int $id
 * @property string $type
 * @property int|null $project_id
 * @property int $user_id
 * @property int|null $task_id
 * @property int|null $payout_id
 * @property string|null $number
 * @property string $date
 * @property string|null $file
 * @property string|null $hash
 * @property int $company_sign_requested
 * @property int $user_sign_requested
 * @property string|null $company_sig
 * @property string|null $user_sig
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $company_id
 * @property-read mixed $is_signed_by_company
 * @property-read mixed $is_signed_by_user
 * @property-read mixed $link
 * @property-read mixed $name
 * @property-read \App\Models\Payout|null $payout
 * @property-read \App\Models\Project|null $project
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Document newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Document newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Document query()
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereCompanySig($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereCompanySignRequested($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document wherePayoutId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereUserSig($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereUserSignRequested($value)
 * @mixin \Eloquent
 */
class Document extends Model
{
    use HasFactory;

    /**
     * Таблица и настройки индекса
     */
    protected $table = "documents";
    protected $primaryKey = "id";
    protected $keyType = "integer";
    public $incrementing = true;
    protected $guarded = ["*"];
    public $timestamps = true;

    public $fillable = [
        'company_sig',
        'company_sign_requested',
        'user_sig',
        'user_sign_requested',
    ];

    public $hidden = [];

    public $appends = [];


    /**
     * Загрузчик класса
     */
    protected static function boot()
    {
        parent::boot();
        //self::observe(new \App\Observers\DocumentObserver);
    }

    /**
     *
     */
    public static function types()
    {
        return [
            'contract' => 'Договор',
            'work_order' => 'Заказ-наряд',
            'act' => 'Акт',
            'signme_anketa' => 'Анкета SignMe',
            'reciept' => 'Чек',
            'other' => 'Другое'
        ];
    }


    /**
     * Исполнитель
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Заказ
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    /**
     * Проект
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    /**
     * Связь с выплатами
     *
     * @return BelongsTo
     */
    public function payout(): BelongsTo
    {
        return $this->belongsTo(Payout::class);
    }


    /**
     * Название документа
     */
    public function getNameAttribute()
    {
        $types = self::types();

        $date = Carbon::parse($this->date)->format('d.m.Y');

        if ($this->type == 'contract') {
            $name = $types[$this->type];
            $name .= ' №' . $this->number . ' от ' . $date;
        } else if (in_array($this->type, ['work_order', 'act', 'reciept'])) {
            $name = $types[$this->type];
            //$name.= ' от ' . $date;
            $name .= ' №' . $this->number . ' от ' . $date;
        } else if ($this->type == 'other') {
            $name = 'Документ';
            $name .= ' от ' . $date;
        } else {
            $name = '';
        }

        return $name;
    }

    public function getIsSignedByCompanyAttribute()
    {
        if ($this->company_sig != false and $this->company_sig != null and $this->company_sig != 0) {
            return true;
        }
        return false;
    }

    public function getIsSignedByUserAttribute()
    {
        if ($this->user_sig != false and $this->user_sig != null and $this->user_sig != 0) {
            return true;
        }
        return false;
    }


    public function getLinkAttribute()
    {
        $urlPrefix = auth()->user()->company_id != null ? 'company' : 'contractor';
        return config('app.url') . "/api/v2/{$urlPrefix}/documents/{$this->id}/download";
    }
}
