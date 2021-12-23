<?php

namespace App\Services;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;

class TinkoffPaymentsApiService
{
    protected string $baseUri;
    protected string $token;

    /**
     * @param string $baseUri
     * @param string $token
     */
    public function __construct(string $baseUri, string $token)
    {
        $this->baseUri = $baseUri;
        $this->token = $token;
    }

    /**
     * @throws \Exception
     */
    public function addCard(array $cardData): array
    {
        return $this->post('/cards/', $cardData);
    }

    /**
     * @throws \Exception
     */
    public function addCustomer(array $customerData): ?array
    {
        return $this->post('/customers', $customerData);
    }

    /**
     * @throws \Exception
     */
    public function initPayment(array $paymentData): ?array
    {
        return $this->post('/payment/init');
    }

    /**
     * @throws \Exception
     */
    public function payment(string $paymentId): ?array
    {
        return $this->post('/payment', [
            'payment_id' => $paymentId
        ]);
    }

    /**
     * @throws \Exception
     */
    public function getPaymentState(string $paymentId, string $clientId): ?array
    {
        return $this->post("/payment/state", [
            'payment_id' => $paymentId,
            'client_id' => $clientId
        ]);
    }

    /**
     * @param string $uri
     * @param array $params
     * @return array|null
     * @throws \Exception
     */
    protected function post(string $uri, array $params=[]): array | null
    {
        $response = $this->authorizedRequest()->post($this->baseUri . $uri, $params);

        if ($response->failed())
            throw new \Exception();

        return $response->json();
    }

    protected function authorizedRequest(): PendingRequest
    {
        return \Http::withHeaders([
            'Authorization' => $this->token
        ])->acceptJson();
    }
}
