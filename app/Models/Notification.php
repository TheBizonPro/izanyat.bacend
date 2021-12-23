<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Notification
 *
 * @property int $id
 * @property int|null $fns_id
 * @property int $user_id
 * @property int $is_readed
 * @property string $from
 * @property string $subject
 * @property string $text
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $created_datetime
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Notification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification query()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereFnsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereIsReaded($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereUserId($value)
 * @mixin \Eloquent
 */
class Notification extends Model
{
    use HasFactory;

    /**
     * Таблица и настройки индекса
     */
    protected $table = "notifications";
    protected $primaryKey = "id";
    protected $keyType = "integer";
    public $incrementing = true;
    protected $guarded = ["*"];
    public $timestamps = true;

    public $fillable = [];

    public $hidden = [];

    public $appends = [
        'created_datetime'
    ];

    protected $casts = [
        'action' => 'array'
    ];


    /**
     * Загрузчик класса
     */
    protected static function boot()
    {
        parent::boot();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }


    public function getCreatedDatetimeAttribute()
    {
        return date('d.m.Y', strtotime($this->created_at)) . " " . date('H:i', strtotime($this->created_at));
    }
}
