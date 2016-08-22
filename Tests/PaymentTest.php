<?php

namespace Riffpoint\LiqpaymentBundle;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PaymentTest extends WebTestCase
{
    private $client;
    private $container;

    public function __construct()
    {
        $this->client = static::createClient();
        $this->container = $this->client->getContainer();
    }


    public function testKeys()
    {

        $publicKey = $this->container->getParameter('liqpay_public_key');
        $privateKey = $this->container->getParameter('liqpay_private_key');

        if (empty($publicKey)) {
            throw new Exception('publicKey is empty');
        }

        if (empty($privateKey)) {
            throw new Exception('privateKey is empty');
        }
    }


    public function testFormpage()
    {
        $crawler = $this->client->request('GET', '/testpayment');
        $this->assertFalse($crawler->filter('html:contains("error")')->count() > 0);
    }

}

