<?php

namespace Mbissonho\BancoInter\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Mbissonho\BancoInter\Model\Config\Backend\AbstractFile;

class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'mbissonho_bancointer_general';

    const MODULE_ENABLED = 'mbissonho_bancointer/mbissonho_bancointer_general/active';

    const CLIENT_ID = 'mbissonho_bancointer/mbissonho_bancointer_general/client_id';
    const CLIENT_SECRET = 'mbissonho_bancointer/mbissonho_bancointer_general/client_secret';
    const CERTIFICATE_FILE = 'mbissonho_bancointer/mbissonho_bancointer_general/certificate_file';
    const SSL_KEY_FILE = 'mbissonho_bancointer/mbissonho_bancointer_general/ssl_key_file';

    const ENVIRONMENT = 'mbissonho_bancointer/mbissonho_bancointer_general/environment';

    const WEBHOOK_URL = 'mbissonho_bancointer/mbissonho_bancointer_general/webhook_url';
    const WEBHOOK_CERTIFICATE_FILE = 'mbissonho_bancointer/mbissonho_bancointer_general/webhook_certificate_file';

    const DEBUG = 'mbissonho_bancointer/mbissonho_bancointer_general/debug';

    protected ScopeConfigInterface $scopeConfig;

    protected Filesystem $filesystem;

    protected EncryptorInterface $encryptor;

    protected ReadInterface $_varDirectory;

    public function __construct(
        Filesystem $filesystem,
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor
    )
    {
        $this->filesystem = $filesystem;
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
        $this->_varDirectory = $this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR);
    }

    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => []
            ]
        ];
    }

    public function isModuleEnabled(int $storeId = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::MODULE_ENABLED,
            'store',
            $storeId
        );
    }

    public function getWebhookUrl(int $storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::WEBHOOK_URL,
            'store',
            $storeId
        );
    }

    public function getClientId(int $storeId = null)
    {
        return $this->encryptor->decrypt($this->scopeConfig->getValue(
            self::CLIENT_ID,
            'store',
            $storeId
        ));
    }

    public function getClientSecret(int $storeId = null)
    {
        return $this->encryptor->decrypt($this->scopeConfig->getValue(
            self::CLIENT_SECRET,
            'store',
            $storeId
        ));
    }

    public function getCertificateFilePath(int $storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::CERTIFICATE_FILE,
            'store',
            $storeId
        );
    }

    public function getSslKeyFilePath(int $storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::SSL_KEY_FILE,
            'store',
            $storeId
        );
    }

    public function getWebhookCertificateFilePath(int $storeId = null)
    {
        return $this->_varDirectory->getAbsolutePath(AbstractFile::UPLOAD_DIR) . '/webhook/' .
            $this->scopeConfig->getValue(
            self::WEBHOOK_CERTIFICATE_FILE,
            'store',
            $storeId
        );
    }

    public function debug(int $storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::DEBUG,
            'store',
            $storeId
        );
    }
}
