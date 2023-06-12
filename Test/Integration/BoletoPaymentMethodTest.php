<?php

declare(strict_types=1);

namespace Mbissonho\BancoInter\Test\Integration;

use Magento\Payment\Model\Method\Adapter as PaymentMethodAdapter;
use Magento\Quote\Api\Data\CartInterface;

class BoletoPaymentMethodTest extends AbstractTestCase
{

    public function testBoletoMethodIsRegistered()
    {
        $boletoPaymentMethodAdapter = $this->getPaymentMethodAdapter();
        $this->assertSame('mbissonho_bancointer_boleto', $boletoPaymentMethodAdapter->getCode());

        /** @var CartInterface $quote */
        $quote = $this->createMock(CartInterface::class);
        $quote->method('getStoreId')->willReturn(0);
        $this->assertFalse($boletoPaymentMethodAdapter->isAvailable($quote));
        $this->assertTrue($boletoPaymentMethodAdapter->canUseCheckout());
    }

    private function getPaymentMethodAdapter(): PaymentMethodAdapter
    {
        return $this->getObjectManager()->get('BancoInterBoletoRemoteMethodAdapter');
    }

}
