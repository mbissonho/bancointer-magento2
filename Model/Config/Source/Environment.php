<?php

namespace Mbissonho\BancoInter\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Environment implements OptionSourceInterface
{
    const PRODUCTION = 'production';

    public function toOptionArray(): array
    {
        return [
            ["value" => self::PRODUCTION, 'label' => __("Production")]
        ];
    }
}
