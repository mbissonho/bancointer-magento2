<?php

namespace Mbissonho\BancoInter\Observer\Adminhtml;

use Magento\Config\Model\Config\Factory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mbissonho\BancoInter\Logger\LoggerInterface;
use Mbissonho\BancoInter\Model\Ui\Boleto\ConfigProvider;
use Mbissonho\BancoInter\Model\Ui\ConfigProvider as GeneralConfigProvider;

class DefineDebugConfigObserver implements ObserverInterface
{
    const PATHS_TO_OBSERVE = [
        'mbissonho_bancointer/mbissonho_bancointer_general/debug'
    ];

    const PATHS_TO_CHANGE_CONFIG = [
        'payment/' . ConfigProvider::CODE . '/debug'
    ];

    protected Factory $configFactory;

    protected GeneralConfigProvider $generalConfigProvider;

    protected LoggerInterface $logger;

    public function __construct(
        GeneralConfigProvider $generalConfigProvider,
        Factory $configFactory,
        LoggerInterface $logger
    )
    {
        $this->generalConfigProvider = $generalConfigProvider;
        $this->configFactory = $configFactory;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        try {
            if(empty(array_intersect(self::PATHS_TO_OBSERVE, $observer->getData('changed_paths')))) {
                return;
            }

            $configModel = $this->configFactory->create();

            foreach (self::PATHS_TO_CHANGE_CONFIG as $path) {
                $configModel->setDataByPath($path, $this->generalConfigProvider->debug(intval($observer->getData('store'))));
            }

            $configModel->addData([
                'section' => 'payment',
                'website' => $observer->getData('website'),
                'store' => $observer->getData('store'),
            ]);

            $configModel->save();

        } catch (\Throwable $e) {
            $this->logger->critical($e);
        }
    }
}
