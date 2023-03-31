<?php

namespace Mbissonho\BancoInter\Logger\Boleto;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Model\Method\Logger as PaymentMethodLogger;
use Psr\Log\LoggerInterface;

class Logger extends PaymentMethodLogger
{
    public function __construct(LoggerInterface $logger, ConfigInterface $config = null)
    {
        parent::__construct($logger, $config);
    }

    public function critical(array $data)
    {
        $this->logger->critical(var_export($data, true));
    }
}
