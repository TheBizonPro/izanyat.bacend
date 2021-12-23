<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\HigherOrderBuilderProxy;
use Illuminate\Database\Eloquent\Model;

/**
 * Модель для работы с Информацией о платформе
 * 
 * Class PlatformInfo
 *
 * @package App\Models
 * @property int $id
 * @property string $partner_id
 * @property string $registration_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $ktir_type
 * @method static \Illuminate\Database\Eloquent\Builder|PlatformInfo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlatformInfo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlatformInfo query()
 * @method static \Illuminate\Database\Eloquent\Builder|PlatformInfo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlatformInfo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlatformInfo whereKtirType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlatformInfo wherePartnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlatformInfo whereRegistrationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlatformInfo whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PlatformInfo extends Model
{
    use HasFactory;

    /**
     * Таблица и настройки индекса
     */
    protected $table = "platform_infos";
    protected $guarded = ['id'];

    /**
     * Получить ID платформы(партнера)
     * @return HigherOrderBuilderProxy|mixed
     */
    public static function getPartnerId()
    {
        return self::query()
            ->select('partner_id')
            ->where('ktir_type', config('npd.ktir_type'))
            ->first()
            ->partner_id;
    }

    /**
     * Обновить информацию о платформе
     *
     * @param string $partnerId
     * @param string $registrationDate
     * @return mixed
     */
    public static function updatePartnerInfo(string $partnerId, string $registrationDate)
    {
        return self::updateOrCreate([
            'ktir_type' => config('npd.ktir_type')
        ],
        [
            'partner_id' => $partnerId,
            'registration_date' => Carbon::parse($registrationDate)
        ]);
    }
}
