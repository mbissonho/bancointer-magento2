<?php

namespace Mbissonho\BancoInter\Controller;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Sales\Model\Order\PaymentFactory as OrderPaymentFactory;
use Magento\Sales\Model\ResourceModel\Order\Payment as OrderPaymentResource;
use Mbissonho\BancoInter\Logger\LoggerInterface;
use Mbissonho\BancoInter\Model\Boleto\PdfDownloader;

class DownloadPdfAction
{

    protected LoggerInterface $logger;

    protected RequestInterface $request;

    protected RedirectFactory $redirectFactory;

    protected OrderPaymentFactory $orderPaymentFactory;

    protected OrderPaymentResource $orderPaymentResource;

    protected PdfDownloader $pdfDownloader;

    public function __construct(
        LoggerInterface $logger,
        RequestInterface $request,
        RedirectFactory $redirectFactory,
        OrderPaymentFactory $orderPaymentFactory,
        OrderPaymentResource $orderPaymentResource,
        PdfDownloader $pdfDownloader
    )
    {
        $this->logger = $logger;
        $this->request = $request;
        $this->redirectFactory = $redirectFactory;
        $this->orderPaymentFactory = $orderPaymentFactory;
        $this->orderPaymentResource = $orderPaymentResource;
        $this->pdfDownloader = $pdfDownloader;
    }

    public function execute()
    {
        try {

            $orderPaymentId = $this->request->getParam('order_payment_id');

            if (!$orderPaymentId) {
                return $this->redirectFactory->create()->setRefererUrl();
            }

            $orderPayment = $this->orderPaymentFactory->create();

            $this->orderPaymentResource->load($orderPayment, $orderPaymentId);

            if (!$orderPayment->getEntityId()) {
                return $this->redirectFactory->create()->setRefererUrl();
            }

            return $this->pdfDownloader->execute($orderPayment);
        } catch (\Throwable $e) {
            $this->logger->critical($e, ['order_payment_id' => $orderPaymentId]);
            return $this->redirectFactory->create()->setRefererUrl();
        }
    }
}
