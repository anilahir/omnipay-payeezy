<?php

namespace Omnipay\Payeezy\Message;

use Omnipay\Common\Message\AbstractResponse;

class Response extends AbstractResponse
{
    public function isSuccessful()
    {
        if (
            (isset($this->data['status']) && $this->data['status'] == "success") &&
            (isset($this->data['token']['value']) && $this->data['token']['value'] != "")
        )
            return true;
        else if (
            (isset($this->data['transaction_status']) && $this->data['transaction_status'] == "approved")
        )
            return true;

        return false;
    }

    public function getCardReference()
    {
        $response = null;

        if (isset($this->data['type']) && isset($this->data['token'])) {
            $response = [
                'type' => $this->data['type'],
                'token' => $this->data['token']
            ];
            $response = json_encode($response);
        }

        return  $response;
    }

    public function getCode()
    {
        return null;
    }

    public function getAuthCode()
    {
        return null;
    }

    public function getTransactionId()
    {
        return isset($this->data['transaction_id']) ? $this->data['transaction_id'] : null;
    }

    public function getTransactionReference()
    {
        return isset($this->data['correlation_id']) ? $this->data['correlation_id'] : null;
    }

    public function getMessage()
    {
        return isset($this->data['Error']['messages']) ? json_encode($this->data['Error']['messages']) : null;
    }

    public function getOrderNumber()
    {
        return null;
    }

    public function getData()
    {
        return json_encode($this->data);
    }
}

