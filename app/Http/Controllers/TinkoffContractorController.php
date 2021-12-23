<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddTinkoffCardRequest;
use App\Http\Requests\CardBindResultRequest;
use App\Services\TinkoffPaymentsApiService;
use App\Services\TinkoffService;

class TinkoffContractorController extends Controller
{
    protected TinkoffPaymentsApiService $tinkoffPaymentsApiClient;
    protected TinkoffService $tinkoffService;

    /**
     * @param TinkoffPaymentsApiService $tinkoffPaymentsApiClient
     * @param TinkoffService $tinkoffService
     */
    public function __construct(TinkoffPaymentsApiService $tinkoffPaymentsApiClient, TinkoffService $tinkoffService)
    {
        $this->tinkoffPaymentsApiClient = $tinkoffPaymentsApiClient;
        $this->tinkoffService = $tinkoffService;
    }


    public function addCard(AddTinkoffCardRequest $request)
    {
        $this->tinkoffPaymentsApiClient->addCard([
            'card_description' => $request['description'],
            'ip' => $request->ip()
        ]);
    }

    public function cardBindResult(CardBindResultRequest $request)
    {
        $requestData = $request->validated();

        if ($request['Status'] === 'REJECTED') {
            $this->tinkoffService->handleBindCardReject($requestData);
        }

        if ($request['Status'] === 'COMPLETED') {
            $this->tinkoffService->handleBindCardSuccess($requestData);
        }

        return 'OK';
    }
}
