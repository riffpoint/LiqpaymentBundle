<?php

/**
 * Class BaseController
 *
 * @author WebCodin <info@webcodin.com>
 * @package Riffpoint\PaymentBundle\Controller
 */

namespace Riffpoint\LiqpaymentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use JMS\DiExtraBundle\Annotation as DI;
use Riffpoint\LiqpaymentBundle;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


class BaseController extends Controller
{
    /**
     * @DI\Inject("request")
     */
    private $request;

    /**
     * @DI\Inject("Liqpay")
     */
    private $Liqpay;

    /**
     * @Route("/testpayment", name="testpayment")
     */
    public function paymentFormAction()
    {

        $this->request;
        $params = [
            'amount' => 1,
            'currency' => 'USD',
            'description' => 'test',
            'server_url' => 'https://test.com/payment-callback',
            'result_url' => 'https://test.com/payment-complete',
            'order_id' => 42,

            'language' => 'EN'
        ];
        $params = $this->Liqpay->cnbParams($params);
        $data = $this->Liqpay->getData($params);
        $signature = $this->Liqpay->getSignature($params);

        $params['data'] = $data;
        $params['signature'] = $signature;
        $params['checkout_url'] = $this->Liqpay->getCheckoutUrl() .'?data=' . $data . '&signature=' . $signature;

        return $this->render('LiqpaymentBundle:Payment:test.html.twig', $params);
    }
}
