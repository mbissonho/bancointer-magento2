<?php

namespace Mbissonho\BancoInter\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mbissonho\BancoInter\Model\Ui\Boleto\ConfigProvider AS BoletoConfigProvider;
use Mbissonho\BancoInter\Model\Ui\ConfigProvider as GeneralConfigProvider;
use Magento\Checkout\Model\Session as CheckoutSession;

class PaymentMethodIsActiveObserver implements ObserverInterface
{
    const METHODS_TO_OBSERVE = [
        BoletoConfigProvider::CODE
    ];

    protected GeneralConfigProvider $generalConfigProvider;
    protected CheckoutSession $checkoutSession;

    public function __construct(
        GeneralConfigProvider $generalConfigProvider,
        CheckoutSession $checkoutSession
    )
    {
        $this->generalConfigProvider = $generalConfigProvider;
        $this->checkoutSession = $checkoutSession;
    }

    public function execute(Observer $observer)
    {
        /* @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getQuote();

        if($quote === null) {
            $quote = $this->checkoutSession->getQuote();
        }

        /* @var \Magento\Payment\Model\MethodInterface $methodInstance */
        $methodInstance = $observer->getMethodInstance();

        if(!$this->generalConfigProvider->isModuleEnabled($quote->getStoreId()) &&
            in_array($methodInstance->getCode(), self::METHODS_TO_OBSERVE)) {
            /* @var \Magento\Framework\DataObject $checkResult */
            $checkResult = $observer->getResult();
            $checkResult->setData('is_available', false);
        }

    }
}
