<?php

declare(strict_types=1);

namespace Mbissonho\BancoInter\Test\Integration;

use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Mbissonho\BancoInter\Model\Ui\Boleto\ConfigProvider as BoletoConfigProvider;
use Magento\Sales\Model\OrderFactory;

abstract class AbstractTestCase extends TestCase
{

    protected function getObjectManager(): ObjectManagerInterface
    {
        return Bootstrap::getObjectManager();
    }

    protected function getOrderWithBoletoPaymentMethod(): OrderInterface
    {
        $order = $this->getObjectManager()->get(OrderFactory::class)->create()
            ->loadByIncrementId('100000001');
        /** @var Payment $payment */
        $payment = $this->getObjectManager()->create(Payment::class);
        $payment->setMethod(BoletoConfigProvider::CODE);;
        $payment->setOrder($order);
        $order->setPayment($payment);
        $order->save();

        return $order;
    }

}
