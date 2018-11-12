<?php

namespace AppBundle\Service;

use AppBundle\Exception\RejectedPaymentException;
use AppBundle\Exception\WebpayException;
use Freshwork\Transbank\CertificationBagFactory;
use Freshwork\Transbank\RedirectorHelper;
use Freshwork\Transbank\TransbankServiceFactory;
use Freshwork\Transbank\WebpayNormal;
use Psr\Log\LoggerInterface;

class WebpayService
{
    /**
     * @var \Twig_Environment
     */
    private $template;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * WebpayService constructor.
     *
     * @param \Twig_Environment $template
     * @param LoggerInterface   $logger
     */
    public function __construct(\Twig_Environment $template, LoggerInterface $logger)
    {
        $this->template = $template;
        $this->logger   = $logger;
    }

    /**
     * Executes the api call to Webpay to do the getTransactionRetult. It returns a temporal transaction to be used in
     * acknowledgeTransaction
     *
     * @param string       $tokenWs
     * @param WebpayNormal $webpayNormal
     *
     * @return \Freshwork\Transbank\WebpayStandard\transactionResultOutput
     * @throws RejectedPaymentException
     * @throws WebpayException
     */
    public function executeTransactionResult(WebpayNormal $webpayNormal, string $tokenWs)
    {
        /*Se verifica con el token el estado de la transaccion*/
        try {
            $transactionResult = $webpayNormal->getTransactionResult($tokenWs);

            if ($transactionResult->detailOutput->responseCode === 0) {
                return $transactionResult;
            } else {
                //orden rechazada
                $this->logger->error('The order was rejected, the response code was: ' . $transactionResult->detailOutput->responseCode);
                throw new RejectedPaymentException(
                    'order has been rejected',
                    $transactionResult->detailOutput->responseCode
                );
            }
        } catch (\SoapFault $exception) {
            throw new WebpayException($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * Executes the real transaction in Webpay. It won't wait longer than 30 seconds, otherwise it will throw
     * a WebpayException
     *
     * @param WebpayNormal $webpayNormal
     *
     * @return \Freshwork\Transbank\WebpayStandard\acknowledgeTransactionResponse
     * @throws WebpayException
     */
    public function acknowledgeTransaction(WebpayNormal $webpayNormal)
    {
        try {
            return $webpayNormal->acknowledgeTransaction();
        } catch (\SoapFault $exception) {
            throw new WebpayException($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @param string $tokenWs
     *
     * @throws RejectedPaymentException
     * @throws WebpayException
     */
    public function redirectToWebpayPayment(string $tokenWs)
    {
        $certificationBag = CertificationBagFactory::integrationWebpayNormal();
        $webpayNormal     = TransbankServiceFactory::normal($certificationBag);

        $transactionResult = null;

        $transactionResult = $this->executeTransactionResult($webpayNormal, $tokenWs);

        $this->acknowledgeTransaction($webpayNormal);
        $redirectHTML = RedirectorHelper::redirectBackNormal($transactionResult->urlRedirection);

        return $this->template->render('@App/webpay/redirectWebpay.html.twig', ['redirect' => $redirectHTML]);
    }
}