<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ProjectUser
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectUser query()
 * @mixin \Eloquent
 */
class ProjectUser extends Model
{
    use HasFactory;

	/**
	 * Таблица и настройки индекса
	 */
	protected $table = "projects_users";
	protected $primaryKey = "id";
	protected $keyType = "integer";
	public $incrementing = true;
	protected $guarded = ["*"];
	public $timestamps = true;
	
	public $fillable = [
		'user_id',
		'project_id',
		'job_category_id'
	];
	
	public $hidden = [];
	
	public $appends = [];


	/**
	 * Загрузчик класса
	 */
	protected static function boot()
	{
		parent::boot();
		//self::observe(new \App\Observers\ProjectUserObserver);
	}
}
