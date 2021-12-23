<?php

namespace App\Http\Controllers\Roles;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignManyRolesToUserRequest;
use App\Http\Requests\AssignRolePermissionsRequest;
use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Spatie\Permission\Exceptions\RoleAlreadyExists;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{

    public function index(Request $request)
    {
        $companyId = $this->getRequesterCompany()->id;
        $query = Role::whereCompanyId($companyId);

        if ($request->get('withPermissions') == 1)
            $query->with('permissions');

        return [
            'roles' => $query->get()
        ];
    }

    public function store(CreateRoleRequest $request)
    {
        $company = $this->getRequesterCompany();

        $roleData = array_merge($request->validated(), [
            'company_id' => $company->id
        ]);

        try {
            return [
                'role' => Role::create($roleData)
            ];
        } catch (RoleAlreadyExists $e) {
            return Response::json([
                'error' => 'Такая роль уже существует'
            ], 422);
        }

    }

    public function show(int $id)
    {
        $role = Role::with('permissions')->findOrFail($id);

        return [
            'role' => $role->toArray()
        ];
    }

    public function update(UpdateRoleRequest $request, int $id)
    {
        $role = Role::findOrFail($id);

        $role->update(
            $request->validated()
        );

        return [
            'role' => $role->toArray()
        ];
    }

    public function assignToRole(AssignRolePermissionsRequest $request, int $roleId)
    {
        $role = Role::findOrFail($roleId);
        $permissions = Permission::whereIn('name', $request['permissions'])->get();

        $role->syncPermissions($permissions);

        return [
            'role' => $role->toArray()
        ];
    }

    public function assignToUser(AssignManyRolesToUserRequest $request, int $userId)
    {
        $roles = Role::findMany($request['roles']);
        $user = User::findOrFail($userId);

        $user->syncRoles($roles);

        return [
            'user' => $user->toArray()
        ];
    }

    public function destroy(int $id)
    {
        $role = Role::findOrFail($id);
        $company = $this->getRequesterCompany();

        $role->delete();

        return [
            'message' => 'Роль удалена'
        ];
    }

    private function getRequesterCompany(): ?\App\Models\Company
    {
        return request()->user()->company;
    }
}
