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
        $hmacAuth = $this->hmacAuthorizationToken($data);
        
        $headers = [
            'Content-Type' => 'application/json',
            'apikey' => $this->getApiKey(),
            'token' => $this->getMerchantToken(),
            'Authorization' => $hmacAuth['authorization'],
            'nonce' => $hmacAuth['nonce'],
            'timestamp' => $hmacAuth['timestamp']
        ];

        if(!empty($data)) {
            $httpResponse = $this->httpClient->request($this->getHttpMethod(), $this->getEndpoint(), $headers, $data);
        }
        else {
            $httpResponse = $this->httpClient->request($this->getHttpMethod(), $this->getEndpoint(), $headers);
        }
    
        try {
            $jsonRes = json_decode($httpResponse->getBody()->getContents(), true);
        }
        catch (\Exception $e){
            info('Guzzle response : ', [$httpResponse]);
            $res = [];
            $res['resptext'] = 'Oops! something went wrong, Try again after sometime.';
            return $this->response = new Response($this, $res);
        }
        
        return $this->response = new Response($this, $jsonRes);
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

