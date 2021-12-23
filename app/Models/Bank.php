<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Bank
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $bik
 * @property string|null $ks
 * @property string|null $index
 * @property string|null $city
 * @property string|null $address
 * @property string|null $okato
 * @property string|null $okpo
 * @property string|null $regnum
 * @property string|null $dateadd
 * @method static \Illuminate\Database\Eloquent\Builder|Bank newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Bank newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Bank query()
 * @method static \Illuminate\Database\Eloquent\Builder|Bank whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bank whereBik($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bank whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bank whereDateadd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bank whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bank whereIndex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bank whereKs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bank whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bank whereOkato($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bank whereOkpo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bank whereRegnum($value)
 * @mixin \Eloquent
 */
class Bank extends Model
{
    use HasFactory;

    /**
     * Таблица и настройки индекса
     */
    protected $table = "banks";
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
        //self::observe(new \App\Observers\BankObserver);
    }
}
