<?php

/**
 * for depositing money using the host-to-host method
 */

namespace sdk_moneygate;

use sdk_moneygate\BaseClass;

class HostToHostDepositing extends BaseClass
{
    public function updateData($data)
    {
        $array = array_merge([
            "id" => $this->getId(),
            "service_id" => 6001,
        ], $data);
        $this->setData($array);
    }

    public function getOptions()
    {
        return [
            'http' => [
                'method' => 'POST',
                'content' => $this->getData(),
                'header' => "X-Auth-Token: " . $this->auth->getXAuthToken() . "\r\n" .
                "X-Auth-Sign: " . $this->auth->get_X_Auth_Sign($this->getData()) . "\r\n" .
                "Content-Type: application/json\r\n" .
                "Accept: application/json'",
            ],
        ];
    }
    public function create(string $callbackUrl = "https://merchant-side.com/send-status-here", int $amount = 100, string $currency = "RUB"): array
    {
        $this->setCallbackUrl($callbackUrl);
        $this->setAmount($amount);
        $this->setCurrency($currency);
        $data = ["data" => [
            "callback_url" => $this->getCallbackUrl(),
            "amount" => $this->getAmount(), // 100
            "currency" => $this->getCurrency(), //"RUB"
        ]];
        $this->updateData($data);
        $context = stream_context_create($this->getOptions());
        $result = file_get_contents($this->getEnviroment() . 'host-to-host/deposit-orders/new', false, $context);
        return json_decode($result, true);
    }

    public function getPaymentInstruments(string $id = null): array
    {
        if ($id) {
            $this->setId($id);
        }
        $this->updateData([]);
        $context = stream_context_create($this->getOptions());
        $result = file_get_contents($this->getEnviroment() . "host-to-host/deposit-orders/get-payment-instruments", false, $context);
        return json_decode($result, true);
    }

    public function setPaymentInstruments(string $id = null, string $paymentType = "card2card", string $bank = '', string $customer_id = '', string $last_card_digits = ''): array
    {
        if ($id) {
            $this->setId($id);
        }

        $paymentInstrument = [
            "payment_type" => $paymentType,
        ];

        $customerData = [
            "customer_id" => $customer_id,
        ];

        if ($paymentType == "card2card") {
            $paymentInstrument["bank"] = $bank;
            $customerData["last_card_digits"] = $last_card_digits;
        } else if ($paymentType == "") {
            $customerData["bank"] = $bank;
        }

        $this->updateData([
            "payment_instrument"=> $paymentInstrument,
            "customer_data"=> $customerData
        ]);
        $context = stream_context_create($this->getOptions());
        $result = file_get_contents($this->getEnviroment() . "host-to-host/deposit-orders/set-payment-instrument", false, $context);
        return json_decode($result, true);
    }

    public function confirm()
    {
        $this->updateData();
        $context = stream_context_create($this->getOptions());
        $result = file_get_contents($this->getEnviroment() . "host-to-host/deposit-orders/confirm", false, $context);
        return $result;
    }

    public function getStatus()
    {
        $this->updateData();
        $context = stream_context_create($this->getOptions());
        $result = file_get_contents($this->getEnviroment() . "host-to-host/withdraw-orders/get-status", false, $context);
        return $result;
    }

}
