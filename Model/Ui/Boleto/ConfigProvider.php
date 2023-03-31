<?php

namespace Mbissonho\BancoInter\Model\Ui\Boleto;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Mbissonho\BancoInter\Model\Ui\ConfigProvider as GeneralConfigProvider;

class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'mbissonho_bancointer_boleto';

    const SHOULD_CANCEL_PAYMENT_WHEN_CANCEL_ORDER = 'payment/mbissonho_bancointer_boleto/cancel_when_cancel_order';

    protected ScopeConfigInterface $scopeConfig;
    protected GeneralConfigProvider $generalConfigProvider;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => []
            ]
        ];
    }

    public function shouldCancelPaymentWhenCancelOrder(int $storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::SHOULD_CANCEL_PAYMENT_WHEN_CANCEL_ORDER,
            'store',
            $storeId
        );
    }

}
