<?php

namespace Riffpoint\LiqpaymentBundle\Service;

use Symfony\Component\Security\Acl\Exception\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;

class Liqpay
{
    private $apiUrl = 'https://www.liqpay.com/api/';
    private $checkoutUrl = 'https://www.liqpay.com/api/3/checkout';
    private $version = 3;
    private $sandbox = 1;

    /**
     * Liqpay constructor.
     *
     * @param Container $container
     * @throws Exception
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $publicKey = $this->container->getParameter('liqpay_public_key');
        $privateKey = $this->container->getParameter('liqpay_private_key');

        if (empty($publicKey)) {
            throw new Exception('publicKey is empty');
        }

        if (empty($privateKey)) {
            throw new Exception('privateKey is empty');
        }
        $this->_public_key = $publicKey;
        $this->_private_key = $privateKey;
    }

    public function isSandbox()
    {
        if ($this->sandbox == 1) {
            return true;
        }
        return false;
    }

    public function getCheckoutUrl()
    {
        return $this->checkoutUrl;
    }

    /**
     * @param $parameters
     *
     * @return mixed
     */
    public function makePay($parameters)
    {
        $res = $this->makeTransaction("payment/pay", $parameters);
        return $res;
    }

    /**
     * @param $path string
     * @param array $params
     * @return mixed
     */

    public function makeTransaction($path, $params = [])
    {
        $params["version"] = $this->version;
        $params["sandbox"] = $this->sandbox;
        $url         = $this->apiUrl . $path;
        $publicKey  = $this->_public_key;
        $privateKey = $this->_private_key;
        $data        = base64_encode(json_encode(array_merge(compact('publicKey'), $params)));
        $signature   = base64_encode(sha1($privateKey.$data.$privateKey, 1));
        $postfields  = http_build_query(array(
            'data'  => $data,
            'signature' => $signature
        ));

        $channel = curl_init();
        curl_setopt($channel, CURLOPT_URL, $url);
        curl_setopt($channel, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($channel, CURLOPT_POST, 1);
        curl_setopt($channel, CURLOPT_POSTFIELDS, $postfields);
        curl_setopt($channel, CURLOPT_RETURNTRANSFER, 1);
        $serverOutput = curl_exec($channel);
        curl_close($channel);
        return json_decode($serverOutput);
    }

    public function getStatus($orderid)
    {
        if (empty($orderid)) {
            throw new Exception('Order ID is empty');
        }
        return $this->makeTransaction("payment/status", ["order_id"=>$orderid]);
    }
    public function getData($params)
    {
        return base64_encode(json_encode($params));
    }

    public function getSignature($params)
    {
        $params['version'] = $this->version;
        $params      = $this->cnbParams($params);
        $privateKey = $this->_private_key;
        $json      = base64_encode(json_encode($params));
        $signature = $this->strToSign($privateKey . $json . $privateKey);
        return $signature;
    }

    /**
     * cnb_signature
     *
     * @param array $params
     *
     * @return string
     */
    public function cnbSignature($params)
    {
        $params      = $this->cnbParams($params);
        $privateKey = $this->_private_key;
        $json      = base64_encode(json_encode($params));
        $signature = $this->strToSign($privateKey . $json . $privateKey);
        return $signature;
    }
    /**
     * cnb_params
     *
     * @param array $params
     *
     * @return array $params
     */
    public function cnbParams($params)
    {

        $params['public_key'] = $this->_public_key;
        $params['version'] = $this->version;
        $params['action'] = 'pay';
        $params['sandbox'] = $this->sandbox;
        if (!isset($params['version'])) {
            throw new \InvalidArgumentException('version is null');
        }
        if (!isset($params['amount'])) {
            throw new \InvalidArgumentException('amount is null');
        }
        if (!isset($params['currency'])) {
            throw new \InvalidArgumentException('currency is null');
        }

        if ($params['currency'] == 'RUR') {
            $params['currency'] = 'RUB';
        }
        if (!isset($params['description'])) {
            throw new \InvalidArgumentException('description is null');
        }

        return $params;
    }

    public function strToSign($str)
    {
        $signature = base64_encode(sha1($str, 1));
        return $signature;
    }

    public function checkSignature($data, $signature)
    {
        if ($signature == base64_encode(sha1(
            $this->_private_key .
            $data .
            $this->_private_key,
            1
        ))) {
            return true;
        }
        return false;
    }
}
