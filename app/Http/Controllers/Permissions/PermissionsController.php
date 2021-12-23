<?php

namespace App\Http\Controllers\Permissions;

use App\Helpers\ArrayHelper;
use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionsController extends Controller
{
    public function index()
    {
//        $permissions = [
//            'company' => [
//                'tasks' => [
//                    'show',
//                    'delete',
//                    'update',
//                    'contractor_assign',
//                    'accept_job',
//                    'pay'
//                ],
//                'payouts' => [
//                    'show',
//                    'repay'
//                ],
//                'documents' => [
//                    'show',
//                    'create',
//                    'request_sign'
//                ],
//                'bank_account' => [
//                    'update'
//                ],
//                'company_data' => [
//                    'update'
//                ],
//                'receipts' => [
//                    'show'
//                ],
//                'projects' => [
//                    'show',
//                    'create'
//                ]
//            ]
//        ];

        return [
            'permissions' => Permission::all()
        ];
    }
}
