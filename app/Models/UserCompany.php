<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\UserCompany
 *
 * @property int $id
 * @property int $user_id
 * @property int $company_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompany newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompany newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompany query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompany whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompany whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompany whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompany whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCompany whereUserId($value)
 * @mixin \Eloquent
 */
class UserCompany extends Model
{
    use HasFactory;

	/**
	 * Таблица и настройки индекса
	 */
	protected $table = "users_companies";
	protected $primaryKey = "id";
	protected $keyType = "integer";
	public $incrementing = true;
	protected $guarded = ["*"];
	public $timestamps = true;
	
	public $fillable = [
		'user_id',
		'company_id'
	];
	
	public $hidden = [];
	
	public $appends = [];


	/**
	 * Загрузчик класса
	 */
	protected static function boot()
	{
		parent::boot();
		//self::observe(new \App\Observers\UserCompanyObserver);
	}
}
