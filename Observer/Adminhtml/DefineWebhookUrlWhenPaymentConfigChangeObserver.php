<?php

namespace Mbissonho\BancoInter\Observer\Adminhtml;

use GuzzleHttp\Exception\ClientException;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mbissonho\BancoInter\Logger\LoggerInterface;
use Mbissonho\BancoInter\Model\Api\Client as ApiClient;
use Mbissonho\BancoInter\Model\Ui\ConfigProvider as GeneralConfigProvider;

class DefineWebhookUrlWhenPaymentConfigChangeObserver implements ObserverInterface
{
    const PATHS_TO_OBSERVE = [
        'mbissonho_bancointer/mbissonho_bancointer_general/webhook_url',
    ];

    protected GeneralConfigProvider $configProvider;

    protected ApiClient $apiClient;

    protected LoggerInterface $logger;

    public function __construct(
        GeneralConfigProvider $configProvider,
        ApiClient $apiClient,
        LoggerInterface $logger
    )
    {
        $this->configProvider = $configProvider;
        $this->apiClient = $apiClient;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        try {

            if(empty(array_intersect(self::PATHS_TO_OBSERVE, $observer->getData('changed_paths')))) {
                return;
            }

            $store = $observer->getData('store');

            $webhookUrl = $this->configProvider->getWebhookUrl(intval($store));

            if(empty($webhookUrl)) {
                return;
            }

            $this->apiClient->defineWebhook($webhookUrl, intval($store));

        } catch (\Throwable|ClientException $e) {
            $this->logger->critical($e);
        }
    }
}
