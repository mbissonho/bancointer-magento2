<?php

namespace Mbissonho\BancoInter\Model\Config\Backend;

class CertificateFile extends AbstractFile
{
    protected function getAllowedExtensions(): array
    {
        return ['crt'];
    }
}
