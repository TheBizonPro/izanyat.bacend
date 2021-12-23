<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\CompanyBankAccount
 *
 * @property int $id
 * @property string|null $mobi_partner_id
 * @property string|null $mobi_secret_password
 * @property int $mobi_connected
 * @property int $company_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|CompanyBankAccount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CompanyBankAccount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CompanyBankAccount query()
 * @method static \Illuminate\Database\Eloquent\Builder|CompanyBankAccount whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CompanyBankAccount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CompanyBankAccount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CompanyBankAccount whereMobiConnected($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CompanyBankAccount whereMobiPartnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CompanyBankAccount whereMobiSecretPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CompanyBankAccount whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CompanyBankAccount extends Model
{
    protected $table = 'companies_bank_accounts';
    protected $primaryKey = 'id';

    protected $fillable = [
        'mobi_partner_id',
        'mobi_secret_password',
        'mobi_connected',
        'company_id'
    ];

    protected $hidden = [
        'mobi_secret_password'
    ];

    public function company()
    {
        return $this->hasOne(Company::class);
    }

}
