<?php

namespace Mbissonho\BancoInter\Observer\Gateway;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;
use Mbissonho\BancoInter\Logger\LoggerInterface;

class IssueBoletoDataAssignObserver extends AbstractDataAssignObserver
{
    protected LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger
    )
    {
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        try {
            $data = $this->readDataArgument($observer);
            $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
            $payment = $this->readPaymentModelArgument($observer);

            if(isset($additionalData['customer_taxvat'])) {
                $payment->setAdditionalInformation('customer_taxvat', $additionalData['customer_taxvat']);
            }
        } catch (\Throwable $e) {
            $this->logger->critical($e);
        }
    }
}
