<?php

namespace Mbissonho\BancoInter\Gateway\Request\Builder;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Store\Model\Store;


class GeneralBuilder extends AbstractBuilder
{
    public function build(array $buildSubject)
    {
        try {
            $paymentDataObject = SubjectReader::readPayment($buildSubject);
            return [
                Store::STORE_ID => $paymentDataObject->getOrder()->getStoreId(),
                'order_increment_id' => $paymentDataObject->getOrder()->getOrderIncrementId()
            ];
        } catch (\Throwable $e) {
            $this->logger->critical($e);
            throw new LocalizedException(__('Error processing payment information'));
        }
    }
}
