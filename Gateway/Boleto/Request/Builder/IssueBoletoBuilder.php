<?php

namespace Mbissonho\BancoInter\Gateway\Boleto\Request\Builder;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Mbissonho\BancoInter\Gateway\Request\Builder\AbstractBuilder;

class IssueBoletoBuilder extends AbstractBuilder
{

    public function build(array $buildSubject): array
    {
        try {

            $paymentDataObject = SubjectReader::readPayment($buildSubject);
            $order = $paymentDataObject->getOrder();
            $storeId = $order->getStoreId();

            $methodInstance = $paymentDataObject->getPayment()->getMethodInstance();

            $numOfDaysToExpire = $methodInstance->getConfigData('expiration_days', $storeId);
            $expirationDate = (new \DateTime())->add(new \DateInterval("P{$numOfDaysToExpire}D"))->format('Y-m-d');

            return [
                'transaction' => [
                    'days_to_cancel_after_expiration' => intval($methodInstance->getConfigData('days_to_cancel_after_expiration', $storeId)),
                    'expiration_date' => $expirationDate,
                    'amount' => $order->getGrandTotalAmount()
                ]
            ];

        } catch (\Throwable $e) {
            $this->logger->critical($e);
            throw new LocalizedException(__('Error processing boleto payment information'));
        }
    }

}
