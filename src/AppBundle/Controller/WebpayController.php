<?php

namespace AppBundle\Controller;

use AppBundle\Form\WebPay\WebpayPayType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Post controller.
 *
 * @Route("webpay")
 */
class WebpayController extends Controller
{
    /**
     * @Route("/", name="webpay_index")
     * @Method("GET")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $form = $this->createForm(
            WebpayPayType::class,
            null,
            [
                'action' => $this->generateUrl('webpay_process_payment'),
                'method' => 'POST',
            ]
        );

        return $this->render(
            '@App/webpay/index.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/pagar", name="webpay_process_payment")
     * @Method("POST")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function pagarAction(Request $request)
    {
        $form = $this->createForm(
            WebpayPayType::class,
            null,
            [
                'action' => $this->generateUrl('webpay_process_payment'),
                'method' => 'POST',
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            return $this->render(
                '@App/webpay/pagar.html.twig',
                [
                    'data' => $data,
                ]
            );
        }

        return $this->render(
            '@App/webpay/index.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }
}
