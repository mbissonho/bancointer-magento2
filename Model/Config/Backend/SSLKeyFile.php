<?php

namespace Mbissonho\BancoInter\Model\Config\Backend;

class SSLKeyFile extends AbstractFile
{
    protected function getAllowedExtensions(): array
    {
        return ['key'];
    }
}
