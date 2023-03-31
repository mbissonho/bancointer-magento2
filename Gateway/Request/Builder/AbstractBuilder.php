<?php

namespace Mbissonho\BancoInter\Gateway\Request\Builder;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Mbissonho\BancoInter\Logger\LoggerInterface;

abstract class AbstractBuilder implements BuilderInterface
{
    protected LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger
    )
    {
        $this->logger = $logger;
    }

}
