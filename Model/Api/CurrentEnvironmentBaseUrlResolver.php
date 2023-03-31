<?php

namespace Mbissonho\BancoInter\Model\Api;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Model\MethodInterface;
use Mbissonho\BancoInter\Model\Config\Source\Environment;
use Mbissonho\BancoInter\Model\Ui\ConfigProvider;

class CurrentEnvironmentBaseUrlResolver
{

    const PRODUCTION_URL = 'https://cdpj.partners.bancointer.com.br';

    protected ScopeConfigInterface $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function getApiBaseUrlByCurrentEnvironment(MethodInterface $method, $storeId)
    {
        return self::PRODUCTION_URL;
    }

    public function getApiBaseUrlByCurrentEnvironmentByScopeConfig($storeId = null)
    {
        return self::PRODUCTION_URL;
    }
}
