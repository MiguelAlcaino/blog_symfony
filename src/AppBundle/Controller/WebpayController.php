<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Transaction;
use AppBundle\Exception\AcknowledgeTransactionException;
use AppBundle\Exception\RejectedPaymentException;
use AppBundle\Exception\TransactionResultException;
use AppBundle\Form\TransactionType;
use AppBundle\Form\WebPay\WebpayPayType;
use Freshwork\Transbank\CertificationBagFactory;
use Freshwork\Transbank\RedirectorHelper;
use Freshwork\Transbank\TransbankServiceFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
        $transaction = new Transaction();
        $form = $this->createForm(
            TransactionType::class,
            $transaction,
            [
                'action' => $this->generateUrl('webpay_process_payment'),
                'method' => 'POST',
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($transaction);
            $em->flush();

            /*
             * En producción se ocupa "create()" en vez de "integrationWebpayNormal()"(certificados de testing) y se
             * especifican mis certificados locales obtenidos desde webpay
             */
            $certificationBag = CertificationBagFactory::integrationWebpayNormal();
            /*
             * Se crea instancia de webpay plus (o webpay normal)
             * */
            $webpayNormal = TransbankServiceFactory::normal($certificationBag);

            /*
             * 1er flujo: inicializar la transacción
             *
             * */
            $webpayNormal->addTransactionDetail($transaction->getAmount(), $transaction->getId()); //monto e id transaccion

            /* returnURL es la url en donde transbank retorna si fue exitoso o no la transaccion
             * finalURL: url donde el usuairo el refirigido cuando termina el proceso (detalle compra ya finalizada)
             * retorna un token(guardar) y una url
             * */
            $response = $webpayNormal->initTransaction($this->generateUrl('webpay_response', [],
                UrlGeneratorInterface::ABSOLUTE_URL), $this->generateUrl('webpay_success', [],
                UrlGeneratorInterface::ABSOLUTE_URL));


            $transaction->setToken($response->token);
            $em->persist($transaction);
            $em->flush();


            $redirectHTML = RedirectorHelper::redirectHTML($response->url, $response->token);

            return $this->render('@App/webpay/redirectWebpay.html.twig', ['redirect' => $redirectHTML]);

        }

        return $this->render(
            '@App/webpay/index.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/response", name="webpay_response", methods={"POST"})
     * @param Request $request
     *
     * @return Response
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function responseAction(Request $request)
    {
        $token_ws = $request->request->get('token_ws');

        try {
            $response = $this->get('webpay_service')->redirectToWebpayPayment($token_ws);
        } catch (RejectedPaymentException $exception) {
            return $this->render(
                '@App/webpay/redirectWebpay.html.twig',
                [
                    'redirect' => 'Tu pago fue rechazado',
                ]
            );
        } catch (TransactionResultException $exception) {
            return $this->render(
                '@App/webpay/redirectWebpay.html.twig',
                [
                    'redirect' => 'Hubo un error tratando de construir tu transaction',
                ]
            );
        } catch (AcknowledgeTransactionException $exception){
            return $this->render(
                '@App/webpay/redirectWebpay.html.twig',
                [
                    'redirect' => 'Hubo un error al registrar tu transaction en webpay',
                ]
            );
        }

        return new Response($response);

    }

    /**
     * @Route("/success", name="webpay_success")
     * @Method({"GET", "POST"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function successAction()
    {
        return $this->render(
            '@App/webpay/redirectWebpay.html.twig',
            [
                'redirect' => "TODO BIENS",
            ]
        );
    }
}
