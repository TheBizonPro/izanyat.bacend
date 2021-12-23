<?php

namespace App\Helpers;

use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\Telegram\TelegramAdminBotClient;

class PermissionsHelper
{
    public static function registerCompanyAdmin(Company $company)
    {
        $signer = $company->signerUser;
        $role = self::createCompanyAdminRole($company);

        $signer->syncRoles($role);
        $signer->save();
    }

    public static function createCompanyAdminRole(Company $company)
    {
        $adminRole = Role::create([
            'name' => "Администратор",
            'company_id' => $company->id
        ]);
        $adminRole->givePermissionTo('company.admin');

        $adminRole->save();

        return $adminRole;

    }
}
