<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\NpdStatus
 *
 * @property int $id
 * @property int $user_id
 * @property int $taxpayer_registred_as_npd
 * @property int $taxpayer_binded_to_platform
 * @property int|null $taxpayer_income_limit_not_exceeded
 * @property string|null $fail_reason_code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|NpdStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NpdStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NpdStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder|NpdStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NpdStatus whereFailReasonCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NpdStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NpdStatus whereTaxpayerBindedToPlatform($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NpdStatus whereTaxpayerIncomeLimitNotExceeded($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NpdStatus whereTaxpayerRegistredAsNpd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NpdStatus whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NpdStatus whereUserId($value)
 * @mixin \Eloquent
 */
class NpdStatus extends Model
{
    use HasFactory;

	/**
	 * Таблица и настройки индекса
	 */
	protected $table = "npd_statuses";
	protected $primaryKey = "id";
	protected $keyType = "integer";
	public $incrementing = true;
	protected $guarded = ["*"];
	public $timestamps = true;
	
	public $fillable = [];
	
	public $hidden = [];
	
	public $appends = [];


	/**
	 * Загрузчик класса
	 */
	protected static function boot()
	{
		parent::boot();
		//self::observe(new \App\Observers\NpdStatusObserver);
	}
}
