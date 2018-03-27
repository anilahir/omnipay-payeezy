<?php

namespace  Omnipay\Payeezy\Message;

abstract class AbstractRequest extends \Omnipay\Common\Message\AbstractRequest
{
    protected $host = 'https://api.payeezy.com/v1/transactions';
    protected $testHost = 'https://api-cert.payeezy.com/v1/transactions';
    protected $endpoint = '';

    public function getEndpoint()
    {
        return $this->getTestMode() ? $this->testHost : $this->host;
    }

    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
    }

    public function getApiKey()
    {
        return $this->getParameter('apiKey');
    }

    public function setApiKey($value)
    {
        return $this->setParameter('apiKey', $value);
    }

    public function getApiSecret()
    {
        return $this->getParameter('apiSecret');
    }

    public function setApiSecret($value)
    {
        return $this->setParameter('apiSecret', $value);
    }

    public function getMerchantToken()
    {
        return $this->getParameter('merchantToken');
    }

    public function setMerchantToken($value)
    {
        return $this->setParameter('merchantToken', $value);
    }

    public function getPaymentMethod()
    {
        return $this->getParameter('payment_method');
    }

    public function setPaymentMethod($value)
    {
        return $this->setParameter('payment_method', $value);
    }

    public function getPaymentProfile()
    {
        return $this->getParameter('payment_profile');
    }

    public function setPaymentProfile($value)
    {
        return $this->setParameter('payment_profile', $value);
    }

    public function getOrderNumber()
    {
        return $this->getParameter('order_number');
    }

    public function setOrderNumber($value)
    {
        return $this->setParameter('order_number', $value);
    }

    protected function getHttpMethod()
    {
        return 'POST';
    }

    public function sendData($data)
    {
        // Don't throe exceptions for 4xx errors
        $this->httpClient->getEventDispatcher()->addListener(
            'request.error',
            function ($event) {
                if($event['response']->isClientError()) {
                    $event->stopPropagation();
                }
            }
        );

        if(!empty($data)) {
            $httpRequest = $this->httpClient->createRequest(
                $this->getHttpMethod(),
                $this->getEndpoint(),
                null,
                $data
            );
        }
        else {
            $httpRequest = $this->httpClient->createRequest(
                $this->getHttpMethod(),
                $this->getEndpoint()
            );
        }

        $hmacAuth = $this->hmacAuthorizationToken($data);

        $httpResponse = $httpRequest
            ->setHeader('apikey', $this->getApiKey())
            ->setHeader('token', $this->getMerchantToken())
            ->setHeader('Content-Type', 'application/json')
            ->setHeader('Authorization', $hmacAuth['authorization'])
            ->setHeader('nonce ', $hmacAuth['nonce'])
            ->setHeader('timestamp ', $hmacAuth['timestamp'])
            ->send();

        return $this->response = new Response($this, $httpResponse->json());
    }

    private function hmacAuthorizationToken($payload)
    {
        $payload = json_encode(json_decode($payload), JSON_FORCE_OBJECT);
        $nonce = strval(hexdec(bin2hex(openssl_random_pseudo_bytes(4, $cstrong))));
        $timestamp = sprintf('%.0f', array_sum(explode(' ', microtime())) * 1000);	// timestamp in milliseconds
        $data = $this->getApiKey() . $nonce . $timestamp . $this->getMerchantToken() . $payload;
        $hashAlgorithm = "sha256";
        $hmac = hash_hmac ( $hashAlgorithm , $data , $this->getApiSecret(), false );    // HMAC Hash in hex
        $authorization = base64_encode($hmac);

        return [
            'authorization' => $authorization,
            'nonce' => $nonce,
            'timestamp' => $timestamp
        ];
    }
}

