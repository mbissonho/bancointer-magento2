<?php

namespace Mbissonho\BancoInter\Test\Integration\Gateway\Boleto\Response;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Mbissonho\BancoInter\Gateway\Boleto\Response\IssueResponseHandler;
use Mbissonho\BancoInter\Test\Integration\Gateway\AbstractGatewayTestCase;

class IssueResponseHandlerTest extends AbstractGatewayTestCase
{
    private ?IssueResponseHandler $issueResponseHandler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->issueResponseHandler = $this->getObjectManager()->get(IssueResponseHandler::class);
    }

    /**
     * @magentoDataFixture   Magento/Sales/_files/order.php
     * @throws LocalizedException
     */
    public function testThatValidGatewayResponseCausesCorrectEffects()
    {
        $order = $this->getOrderWithBoletoPaymentMethod();

        $this->issueResponseHandler->handle(
            [
                'payment' => $this->getNewPaymentDataObjectFromOrder($order),
                'stateObject' => new DataObject
            ],
            [
                'body' => json_encode([
                    "seuNumero" => "1234",
                    "nossoNumero" => "98754",
                    "codigoBarras" => "5555555555555555554",
                    "linhaDigitavel" => "444444444444444445",
                ]),
            ]
        );

        $payment = $order->getPayment();

        self::assertEquals("98754", $payment->getTransactionId());
        self::assertFalse($payment->getIsTransactionClosed());
    }

}
