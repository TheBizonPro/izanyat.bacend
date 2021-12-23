<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Project
 *
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company $company
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Task[] $tasks
 * @property-read int|null $tasks_count
 * @method static \Illuminate\Database\Eloquent\Builder|Project newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Project newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Project query()
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Project extends Model
{
    use HasFactory;

    /**
     * Таблица и настройки индекса
     */
    protected $table = "projects";
    protected $primaryKey = "id";
    protected $keyType = "integer";
    public $incrementing = true;
    protected $guarded = ["*"];
    public $timestamps = true;

    public $fillable = [
        'company_id',
        'name',
    ];

    public $hidden = [];

    public $appends = [];


    /**
     * Загрузчик класса
     */
    protected static function boot()
    {
        parent::boot();
        //self::observe(new \App\Observers\ProjectObserver);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_projects');
    }


    public function company()
    {
        return $this->belongsTo(Company::class, "company_id", "id");
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'project_id', "id");
    }

    //public function users()
    //{
    //	return $this->belongsToMany(User::class, "projects_users", "project_id", "user_id");
    //}

    public function tasks()
    {
        return $this->hasMany(Task::class, "project_id", "id");
    }

    public function orders()
    {
        return $this->hasMany(Order::class, "project_id", "id");
    }
}
