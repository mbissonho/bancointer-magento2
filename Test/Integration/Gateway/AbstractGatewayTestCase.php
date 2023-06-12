<?php

namespace Mbissonho\BancoInter\Test\Integration\Gateway;

use Magento\Payment\Gateway\Data\PaymentDataObjectFactoryInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Mbissonho\BancoInter\Test\Integration\AbstractTestCase;

abstract class AbstractGatewayTestCase extends AbstractTestCase
{

    protected function getNewPaymentDataObjectFromOrder(OrderInterface $order): PaymentDataObjectInterface
    {
        /** @var PaymentDataObjectFactoryInterface $paymentDataObjectFactory */
        $paymentDataObjectFactory = $this->getObjectManager()->get(PaymentDataObjectFactoryInterface::class);
        return $paymentDataObjectFactory->create($order->getPayment());
    }

}
