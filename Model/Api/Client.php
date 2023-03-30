<?php

namespace Mbissonho\BancoInter\Model\Api;

use GuzzleHttp\Client as HttpClient;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Mbissonho\BancoInter\Model\Config\Backend\AbstractFile;
use Mbissonho\BancoInter\Model\Ui\ConfigProvider as GeneralConfigProvider;
use Psr\Http\Message\ResponseInterface;

class Client
{
    protected CurrentEnvironmentBaseUrlResolver $currentEnvironmentBaseUrlResolver;

    protected HttpClient $client;

    protected GeneralConfigProvider $generalConfigProvider;

    protected Filesystem $filesystem;

    protected ReadInterface $_varDirectory;

    protected ?string $authToken = null;

    public function __construct(
        Filesystem $filesystem,
        GeneralConfigProvider $configProvider,
        CurrentEnvironmentBaseUrlResolver $currentEnvironmentBaseUrlResolver
    )
    {
        $this->filesystem = $filesystem;
        $this->generalConfigProvider = $configProvider;
        $this->currentEnvironmentBaseUrlResolver = $currentEnvironmentBaseUrlResolver;
        $this->client = new HttpClient(
            ['base_uri' => $this->currentEnvironmentBaseUrlResolver->getApiBaseUrlByCurrentEnvironmentByScopeConfig()]
        );

        $this->_varDirectory = $this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR);
    }

    public function generateAuthToken($storeId = null): void
    {
        if($this->authToken != null) {
            return;
        }

        $this->recreateHttpClientIfNeed($storeId);

        $response = $this->client->post('/oauth/v2/token', [
            'form_params' => [
                'client_id' => $this->generalConfigProvider->getClientId($storeId),
                'client_secret' => $this->generalConfigProvider->getClientSecret($storeId),
                'grant_type' => 'client_credentials',
                'scope' => 'boleto-cobranca.read boleto-cobranca.write'
            ],
            'cert' => $this->_varDirectory->getAbsolutePath(AbstractFile::UPLOAD_DIR) . '/' .
                $this->generalConfigProvider->getCertificateFilePath($storeId),
            'ssl_key' => $this->_varDirectory->getAbsolutePath(AbstractFile::UPLOAD_DIR) . '/' .
                $this->generalConfigProvider->getSslKeyFilePath($storeId)
        ]);

        if($response->getStatusCode() == 200) {
            $this->authToken = json_decode($response->getBody()->getContents())->access_token;
        }
    }

    public function issueBoleto(array $requestBody, $storeId = null): ResponseInterface
    {
        $this->generateAuthToken($storeId);

        return $this->client->post(
            '/cobranca/v2/boletos',
            [
                'json' => $requestBody,
                'cert' => $this->_varDirectory->getAbsolutePath(AbstractFile::UPLOAD_DIR) . '/' .
                    $this->generalConfigProvider->getCertificateFilePath($storeId),
                'ssl_key' => $this->_varDirectory->getAbsolutePath(AbstractFile::UPLOAD_DIR) . '/' .
                    $this->generalConfigProvider->getSslKeyFilePath($storeId),
                'headers' => [
                    'Authorization' => "Bearer {$this->authToken}"
                ]
            ]
        );
    }

    public function defineWebhook(string $webhookUrl, $storeId = null): void
    {
        $this->generateAuthToken($storeId);
        $this->recreateHttpClientIfNeed($storeId);

        $this->client->put('/cobranca/v2/boletos/webhook', [
            'json' => ['webhookUrl' => $webhookUrl],
            'cert' => $this->_varDirectory->getAbsolutePath(AbstractFile::UPLOAD_DIR) . '/' .
                $this->generalConfigProvider->getCertificateFilePath($storeId),
            'ssl_key' => $this->_varDirectory->getAbsolutePath(AbstractFile::UPLOAD_DIR) . '/' .
                $this->generalConfigProvider->getSslKeyFilePath($storeId),
            'headers' => [
                'Authorization' => "Bearer {$this->authToken}"
            ]
        ]);
    }

    public function cancelPayment($ourNumber, $storeId = null): ResponseInterface
    {
        $this->generateAuthToken($storeId);
        $this->recreateHttpClientIfNeed($storeId);

        return $this->client->post(sprintf("/cobranca/v2/boletos/%s/cancelar", $ourNumber), [
            'json' => ['motivoCancelamento' => 'ACERTOS'],
            'cert' => $this->_varDirectory->getAbsolutePath(AbstractFile::UPLOAD_DIR) . '/' .
                $this->generalConfigProvider->getCertificateFilePath($storeId),
            'ssl_key' => $this->_varDirectory->getAbsolutePath(AbstractFile::UPLOAD_DIR) . '/' .
                $this->generalConfigProvider->getSslKeyFilePath($storeId),
            'headers' => [
                'Authorization' => "Bearer {$this->authToken}"
            ]
        ]);
    }

    public function getBoletoPdf($ourNumber, $storeId = null): ResponseInterface
    {
        $this->generateAuthToken($storeId);
        $this->recreateHttpClientIfNeed($storeId);

        return $this->client->get(sprintf("/cobranca/v2/boletos/%s/pdf", $ourNumber), [
            'headers' => [
                'Authorization' => "Bearer {$this->authToken}"
            ],
            'cert' => $this->_varDirectory->getAbsolutePath(AbstractFile::UPLOAD_DIR) . '/' .
                $this->generalConfigProvider->getCertificateFilePath($storeId),
            'ssl_key' => $this->_varDirectory->getAbsolutePath(AbstractFile::UPLOAD_DIR) . '/' .
                $this->generalConfigProvider->getSslKeyFilePath($storeId)
        ]);
    }

    public function getPaymentCollection($initialDate, $finalDate, $storeId = null): array
    {
        $this->generateAuthToken($storeId);
        $this->recreateHttpClientIfNeed($storeId);

        $response = $this->client->get(
            sprintf("/cobranca/v2/boletos?filtrarDataPor=EMISSAO&dataInicial=%s&dataFinal=%s", $initialDate, $finalDate),
            //sprintf("/cobranca/v2/boletos?filtrarDataPor=EMISSAO&situacao=PAGO&dataInicial=%s&dataFinal=%s", $initialDate, $finalDate),
            [
                'headers' => [
                    'Authorization' => "Bearer {$this->authToken}"
                ],
                'cert' => $this->_varDirectory->getAbsolutePath(AbstractFile::UPLOAD_DIR) . '/' .
                    $this->generalConfigProvider->getCertificateFilePath($storeId),
                'ssl_key' => $this->_varDirectory->getAbsolutePath(AbstractFile::UPLOAD_DIR) . '/' .
                    $this->generalConfigProvider->getSslKeyFilePath($storeId)
            ]
        );

        $payments = [];

        $responseAsJson = json_decode($response->getBody()->getContents());

        foreach ($responseAsJson->content as $payment) {
            $payments[$payment->nossoNumero] = $payment;
        }

        return $payments;
    }

    private function recreateHttpClientIfNeed($storeId = null): void
    {
        if(!is_null($storeId)) {
            $this->client = new HttpClient(
                ['base_uri' => $this->currentEnvironmentBaseUrlResolver->getApiBaseUrlByCurrentEnvironmentByScopeConfig($storeId)]
            );
        }
    }

}
