<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\JobCategory
 *
 * @property int $id
 * @property int|null $parent_id
 * @property string $name
 * @property int $is_active
 * @property int $sort
 * @method static \Illuminate\Database\Eloquent\Builder|JobCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JobCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JobCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|JobCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobCategory whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobCategory whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobCategory whereSort($value)
 * @mixin \Eloquent
 */
class JobCategory extends Model
{
    use HasFactory;

	/**
	 * Таблица и настройки индекса
	 */
	protected $table = "job_categories";
	protected $primaryKey = "id";
	protected $keyType = "integer";
	public $incrementing = true;
	protected $guarded = ["*"];
	public $timestamps = false;
	
	public $fillable = [];
	
	public $hidden = [];
	
	public $appends = [];


	/**
	 * Загрузчик класса
	 */
	protected static function boot()
	{
		parent::boot();
		//self::observe(new \App\Observers\JobCategory);
	}
}
