<?php

namespace App\Services;

use App\Helpers\ArrayHelper;
use App\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionsHelper
{
    public static function setGlobalPermissionsList()
    {
        Permission::where('id', '>', 0)->delete();
        Role::where('id', '>', 0)->delete();

        $permissions = [
            'company' => [
                'admin',
                'tasks' => [
                    'show',
                    'delete',
                    'update',
                    'contractor_assign',
                    'accept_job',
                    'pay',
                ],
                'payouts' => [
                    'show',
                    'repay'
                ],
                'documents' => [
                    'show',
                    'create',
                    'request_sign'
                ],
                'bank_account' => [
                    'update'
                ],
                'company_data' => [
                    'update'
                ],
                'receipts' => [
                    'show'
                ],
                'projects' => [
                    'show',
                    'create'
                ],
            ]
        ];

        $permissionsStrings = ArrayHelper::flattenJoin($permissions);

        foreach ($permissionsStrings as $permissionsString) {
            \Spatie\Permission\Models\Permission::create(['name' => $permissionsString, 'guard_name' => 'api']);
        }
    }
}
