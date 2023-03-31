<?php

namespace Mbissonho\BancoInter\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\File;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\WriteInterface;

abstract class AbstractFile extends File
{
    const UPLOAD_DIR = 'mbissonho/bancointer';

    protected WriteInterface $_varDirectory;

    protected function _getAllowedExtensions()
    {
        return $this->getAllowedExtensions();
    }

    protected function getUploadDirPath($uploadDir)
    {
        $this->_varDirectory = $this->_filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        return $this->_varDirectory->getAbsolutePath($uploadDir);
    }

    abstract protected function getAllowedExtensions(): array;
}
