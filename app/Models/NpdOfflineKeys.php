<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Модель для работы с Оффлайн ключами
 * 
 * Class NpdOfflineKeys
 *
 * @package App\Models
 * @property int $id
 * @property int $user_id
 * @property int $sequence_number
 * @property string $hash_key
 * @property string $expire_time
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\User $users
 * @method static \Illuminate\Database\Eloquent\Builder|NpdOfflineKeys newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NpdOfflineKeys newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NpdOfflineKeys query()
 * @method static \Illuminate\Database\Eloquent\Builder|NpdOfflineKeys whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NpdOfflineKeys whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NpdOfflineKeys whereExpireTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NpdOfflineKeys whereHashKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NpdOfflineKeys whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NpdOfflineKeys whereSequenceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NpdOfflineKeys whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NpdOfflineKeys whereUserId($value)
 * @mixin \Eloquent
 */
class NpdOfflineKeys extends Model
{
    use HasFactory;

    /**
     * Таблица и настройки индекса
     */
    protected $table = "npd_offline_keys";
    protected $primaryKey = "id";
    protected $keyType = "integer";
    protected $guarded = ['id'];

    protected $fillable = [
        'user_id',
        'sequence_number',
        'hash_key',
        'expire_time',
        'deleted_at',
    ];

    public $incrementing = true;
    public $timestamps = true;

    const COUNT_OF_KEYS_PER_REQUEST = 50;
    const REMAINDER = self::COUNT_OF_KEYS_PER_REQUEST / 10;

    /**
     * Связь с таблицей пользователей
     *
     * @return BelongsTo
     */
    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
