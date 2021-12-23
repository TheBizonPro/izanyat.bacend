<?php

namespace App\Services\Fns;

use Illuminate\Support\Facades\Http;

class FNSService
{

    private string $host;

    public function __construct()
    {
        $this->host = config('fns.fns_microservice');
    }

    /**
     * @param string $inn
     * @param string|null $receipt_id
     * @param string $request_time
     * @param string $operation_time
     * @param string $customer_inn
     * @param string $customer_organization
     * @param string $amount
     * @param string $name
     * @param string $total_amount
     * @param string|null $receipt_url
     * @param string|null $receipt_uuid
     * @return \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
     */
    public function incomeFiscalization(
        string $inn,
        string $receipt_id = null,
        string $request_time,
        string $operation_time,
        string $customer_inn,
        string $customer_organization,
        string $amount,
        string $name,
        string $total_amount,
        string $receipt_url = null,
        string $receipt_uuid = null
    ) {
        $address = "{$this->host}/fiscal";

        //        Log::channel('debug')->debug("FISCAL TIME {$request_time}");

        $dataToSend = [
            'inn' => $inn,
            'request_time' => $request_time,
            'operation_time' => $operation_time,
            'customer_inn' => $customer_inn,
            'customer_organization' => $customer_organization,
            'amount' => $amount,
            'name' => $name,
            'total_amount' => $total_amount,
            'receipt_id' => $receipt_id,
            'receipt_url' => $receipt_url,
            'receipt_uuid' => $receipt_uuid,
        ];

        return Http::asForm()->post($address, $dataToSend);
    }

    public function annulateReceipt(string $inn, string $receiptID, string $reasonCode)
    {
        $address = "{$this->host}/receipt/annulate";

        return Http::asForm()->post($address, [
            'inn' => $inn,
            'receipt_id' => $receiptID,
            'reason_code' => $reasonCode
        ]);
    }

    public function receiptIncome(string $inn)
    {
        $address = "{$this->host}/receipt/income";

        return Http::asForm()->post($address, [
            'inn' => $inn
        ]);
    }

    public function taxpayerBind(string $inn)
    {
        $address = "{$this->host}/taxpayer/bind";

        return Http::asForm()->post($address, [
            'inn' => $inn
        ]);
    }

    public function taxpayerBindStatus($id)
    {
        $address = "{$this->host}/taxpayer/check";

        return Http::asForm()->post($address, [
            'id' => $id
        ]);
    }

    public function taxpayerUnbind(string $inn)
    {
        $address = "{$this->host}/taxpayer/unbind";

        return Http::asForm()->post($address, [
            'inn' => $inn
        ]);
    }

    public function taxpayerStatusCheck(string $id)
    {
        $address = "{$this->host}/taxpayer/check";

        return Http::asForm()->post($address, [
            'id' => $id
        ]);
    }


    public function taxpayerStatus(string $inn)
    {
        $address = "{$this->host}/taxpayer/status";

        return Http::asForm()->post($address, [
            'inn' => $inn
        ]);
    }

    public function taxpayerStatusNDP(string $inn)
    {
        $address = "{$this->host}/taxpayer/ndp";

        return Http::asForm()->post($address, [
            'inn' => $inn
        ]);
    }

    public function offlineKeys(array $inns)
    {
        $address = "{$this->host}/keys/offline";

        return Http::asForm()->post($address, ['inn' => $inns]);
    }

    public function getNotifications(string $inn)
    {
        $address = "{$this->host}/notifications";

        return Http::asForm()->post($address, [
            'inn' => $inn
        ]);
    }

    public function readAllNotifications(string $inn)
    {
        $address = "{$this->host}/notifications/read";

        return Http::asForm()->post($address, [
            'inn' => $inn
        ]);
    }

    public function readNotification(string $inn, string $messageID)
    {
        $address = "{$this->host}/notification/read/${messageID}";

        return Http::asForm()->post($address, [
            'inn' => $inn
        ]);
    }

    public function platformRegistration()
    {
        $address = "{$this->host}/platform/registration";

        return Http::asForm()->post($address, []);
    }

    public function newlyUnboundTaxpayer($from, $to, $limit, $offset)
    {
        $address = "{$this->host}/taxpayer/newly/unbound";

        return Http::asForm()->post($address, [
            'from' => $from,
            'to' => $to,
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    public function notificationDelivered($inn, $fnsID)
    {
        $address = "{$this->host}/notifications/delivered";

        return Http::asForm()->post($address, [
            'inn' => $inn,
            'fns_id' => $fnsID
        ]);
    }

    public function yearIncome($inn, $taxPeriodID)
    {
        $address = "{$this->host}/taxpayer/income";

        return Http::asForm()->post($address, [
            'inn' => $inn,
            'fns_id' => $fnsID
        ]);
    }

    public function categories()
    {
        $address = "{$this->host}/platform/categories";

        return Http::asForm()->post($address);
    }
}
