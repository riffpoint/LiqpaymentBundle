<?php

namespace Riffpoint\LiqpaymentBundle\Service;

class TwigLiqpayPaymentForm extends \Twig_Extension
{

    /**
     * Return the functions registered as twig extensions
     *
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction(
                'liqpay_payment_form',
                [$this, 'getPaymentForm'],
                [
                'is_safe' => array('html'),
                'needs_environment' => true
                ]
            ),
        );
    }

    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
        $this->environment->getLoader()->addPath(__DIR__ . '/../Resources/views');
    }

    public function getName()
    {
        return "liqpay_payment_form";
    }
    public function getPaymentForm(\Twig_Environment $twig, $checkoutUrl, $data, $signature, $language)
    {
        return $twig->render('LiqpaymentBundle:liqpay:payment_form.html.twig', [
            "checkout_url"=>$checkoutUrl,
            "data"=>$data,
            "signature"=>$signature,
            "language"=>$language
        ]);
    }
}
