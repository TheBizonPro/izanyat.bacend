<?php

namespace App\Services;

use Exception;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use SoapClient;

use Illuminate\Support\Facades\Log;

class MobiPaymentsService
{
    protected SoapClient $soapClient;
    protected string $partnerId;
    protected string $secretPassword;

    public function __construct(string $partnerId, string $secretPassword)
    {
        try {
            $this->soapClient = new SoapClient('https://api.nkomobi.ru:9443/MassPaymentService.asmx?WSDL');
            $this->partnerId = $partnerId;
            $this->secretPassword = $secretPassword;
            Log::channel('debug')->debug("Sending to https://api.nkomobi.ru:9443/MassPaymentService.asmx?WSDL");
            Log::channel('debug')->debug("Partner ID is $partnerId");
            Log::channel('debug')->debug("Secret password is $secretPassword");
        } catch (\Exception $e) {
            Log::channel('debug')->debug('словили эксепшн : ' . $e->getMessage());
        }
    }

    /**
     * @param array $person
     * @return array
     * @throws Exception
     */
    public function initPersonRegistration(array $person): array
    {
        return $this->call('InitPersonRegistration', [
            'Person' => $person
        ]);
    }

    public function initPaymentCover(array $paymentData): array
    {
        return $this->call('InitPaymentCover', $paymentData);
    }

    /**
     * @param array $person
     * @return array
     * @throws Exception
     */
    public function getPersonRegistrationStatus(array $person): array
    {
        return $this->call('GetPersonRegistrationStatus', [
            'Person' => $person
        ]);
    }

    /**
     * @throws Exception
     */
    public function getBalance(): array
    {
        return $this->call('GetBalance');
    }

    /**
     * @param array $paymentDetail
     * @return array
     * @throws Exception
     */
    public function initPaymentEasy(array $paymentDetail): array
    {
//        Log::channel('debug')->debug('payment process',$this->call('InitPaymentEasy', $paymentDetail));
        return $this->call('InitPaymentEasy', $paymentDetail);
    }

    /**
     * @param string $orderId
     * @return array
     * @throws Exception
     */
    public function getPaymentStatus(string $orderId): array
    {
        Log::channel('debug')->debug('payment status');
        return $this->call('GetPaymentStatus', [
            'OrderID' => $orderId
        ]);
    }

    /**
     * @param string $method
     * @param array $params
     * @param bool $withPartnerId
     * @return array
     */
    protected function call(string $method, array $params=[], bool $withPartnerId=true): array
    {
        $message = $params;
        if ($withPartnerId) {
            $message = array_merge(['PartnerID' => $this->partnerId], $message);
        }
        $message['Signature'] = $this->createSign($message);
        $response =  $this->soapClient->__soapCall($method, ['Message' => $message]);
        return (array) $response;
    }

    /**
     * @param array $args
     * @return string
     */
    public function createSign(array $args): string
    {
        $signParams = [];
        $flattenArrayIterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($args));

        foreach ($flattenArrayIterator as $item) {
            if (is_bool($item))
                $item = $item ? 'true' : 'false';

            $signParams[] = $item;
        }

        $signParams[] = $this->secretPassword;

        $sign = join('', $signParams);

        return base64_encode(md5($sign, true));
    }

    public function getSoapClient(): SoapClient
    {
        return $this->soapClient;
    }
}
