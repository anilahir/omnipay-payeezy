<?php

namespace Omnipay\Payeezy\Message;

class CreateCardRequest extends AbstractRequest
{
    public function getEndpoint()
    {
        $endPoint =  $this->getTestMode() ? $this->testHost : $this->host;
        return $endPoint . '/tokens';
    }

    public function getData()
    {
        $data = [];
        $this->getCard()->validate();

        if($this->getCard()) {

            $brand = $this->getCard()->getBrand();

            if ($brand == 'amex') $brand = 'American Express';

            $data = [
                'type' => 'FDToken',
                'credit_card' => [
                    'type' => $brand,
                    'cardholder_name' => $this->getCard()->getName(),
                    'card_number' => $this->getCard()->getNumber(),
                    'exp_date' => $this->getCard()->getExpiryDate('my'),
                    'cvv' => $this->getCard()->getCvv()
                ],
                'auth' => 'false',
                'ta_token' => 'NOIW'
            ];
        }

        return json_encode($data);
    }
}
