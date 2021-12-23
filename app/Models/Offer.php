<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Offer
 *
 * @property int $id
 * @property int $project_id
 * @property int $task_id
 * @property int $user_id
 * @property int|null $accepted
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Project $project
 * @property-read \App\Models\Task $task
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Offer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Offer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Offer query()
 * @method static \Illuminate\Database\Eloquent\Builder|Offer whereAccepted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Offer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Offer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Offer whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Offer whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Offer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Offer whereUserId($value)
 * @mixin \Eloquent
 */
class Offer extends Model
{
    use HasFactory;

    /**
     * Таблица и настройки индекса
     */
    protected $table = "offers";
    protected $primaryKey = "id";
    protected $keyType = "integer";
    public $incrementing = true;
    protected $guarded = ["*"];
    public $timestamps = true;

    public $fillable = [
        'task_id',
        'user_id',
        'project_id',
        'accepted',
    ];

    public $hidden = [];

    public $appends = [];


    /**
     * Загрузчик класса
     */
    protected static function boot()
    {
        parent::boot();
        //self::observe(new \App\Observers\OfferObserver);
    }




    /**
     * Исполнитель
     */
    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id', 'id');
    }



    /**
     * Исполнитель
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Проект
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }
}
