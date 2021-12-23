<?php

namespace App\Http\Controllers\CompanyBankAccount;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyBankAccountRequest;
use App\Models\Company;
use App\Models\CompanyBankAccount;
use App\Services\MobiService;
use App\Services\PaymentsService;
use Illuminate\Http\Request;
use JetBrains\PhpStorm\ArrayShape;

class CompanyBankAccountController extends Controller
{
    protected PaymentsService $paymentsService;

    /**
     * @param PaymentsService $paymentsService
     */
    public function __construct(PaymentsService $paymentsService)
    {
        $this->paymentsService = $paymentsService;
    }

    public function get(Request $request): array
    {
        $companyId = $request->user()->company_id;
        return CompanyBankAccount::whereCompanyId($companyId)->firstOrFail()->toArray();
    }

    #[ArrayShape(['bank_account' => "array"])]
    public function store(StoreCompanyBankAccountRequest $request): \Illuminate\Http\JsonResponse | array
    {
        $company = $request->user()->company;

        try {
//            $mobiService = new MobiService($company);
           $bankAccount = $this->paymentsService->setCompanyCredentials(
               $company,
               $request['mobi_partner_id'],
               $request['mobi_secret_pass']
           );

           return [
             'bank_account' => $bankAccount
           ];
        } catch (\Exception $exception) {
            return response()->json([
                'error' => $exception->getMessage()
            ], 400);
        }
    }
}
