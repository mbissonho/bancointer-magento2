<?php

namespace Mbissonho\BancoInter\Logger;

use Magento\Framework\Logger\Monolog;

class Logger extends Monolog implements LoggerInterface
{
    public function __construct($name, array $handlers = [], array $processors = [])
    {
        parent::__construct($name, [end($handlers)], $processors);
    }
}
