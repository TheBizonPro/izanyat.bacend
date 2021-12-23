<?php

namespace App\Http\Controllers\User;

use App\Http\Requests\CreateEmployeeRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Log;

class EmployeesController
{
    private $companyId;

    public function __construct()
    {
        $this->companyId = request()->user()->company_id;
    }

    public function index(Request $request)
    {
        return [
            'employees' => User::whereCompanyId($this->companyId)->with('roles')->get()
        ];
    }

    public function employee(Request $request)
    {
        $employee = User::whereCompanyId($this->companyId)->with('roles')->findOrFail($request->employee_id);
        return response()->json($employee);
    }

    public function store(CreateEmployeeRequest $request)
    {
        $requestData = $request->validated();

        $employeeData = array_merge($requestData, [
            'company_id' => $this->companyId,
            'is_client' => 1
        ]);

        $employee = User::create($employeeData);

        return [
            'employee' => $employee->toArray()
        ];
    }

    public function destroy(int $id)
    {
        User::findOrFail($id)->delete();


        //TODO удаление пользователя из signme

        return [
            'message' => 'Пользователь успешно удален'
        ];
    }
}
