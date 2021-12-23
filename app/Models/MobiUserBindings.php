<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\MobiUserBindings
 *
 * @property int $id
 * @property int $user_id
 * @property int $company_id
 * @property string|null $mobi_confirm_id
 * @property int|null $is_identified
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|MobiUserBindings newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MobiUserBindings newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MobiUserBindings query()
 * @method static \Illuminate\Database\Eloquent\Builder|MobiUserBindings whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MobiUserBindings whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MobiUserBindings whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MobiUserBindings whereIsIdentified($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MobiUserBindings whereMobiConfirmId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MobiUserBindings whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MobiUserBindings whereUserId($value)
 * @mixin \Eloquent
 */
class MobiUserBindings extends Model
{
    protected $table = 'mobi_users_bindings';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'company_id',
        'mobi_confirm_id',
        'is_identified',
    ];

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function company()
    {
        return $this->hasOne(Company::class);
    }
}
