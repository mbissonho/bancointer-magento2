<?php

namespace Mbissonho\BancoInter\Model\Boleto;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\ResourceModel\Order\Payment as OrderPaymentResource;
use Mbissonho\BancoInter\Model\Api\Client;

class PdfDownloader
{
    protected HttpClient $httpClient;

    protected FileFactory $fileFactory;

    protected Filesystem $filesystem;

    protected OrderPaymentResource $orderPaymentResource;

    protected Client $client;

    private ReadInterface $_varDirectory;

    public function __construct(
        HttpClient $httpClient,
        FileFactory $fileFactory,
        Filesystem $filesystem,
        OrderPaymentResource $orderPaymentResource,
        Client $client
    )
    {
        $this->httpClient = $httpClient;
        $this->fileFactory = $fileFactory;
        $this->filesystem = $filesystem;
        $this->orderPaymentResource = $orderPaymentResource;
        $this->client = $client;

        $this->_varDirectory = $this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR);
    }

    /**
     * @throws GuzzleException
     * @throws LocalizedException
     */
    public function execute(OrderPaymentInterface $orderPayment)
    {
        $ourNumber = $orderPayment->getAdditionalInformation('our_number');

        if($base64Pdf = $orderPayment->getAdditionalInformation('pdf_as_base64')) {
            return $this->fileFactory->create(
                $ourNumber . '.pdf',
                base64_decode($base64Pdf,true),
                DirectoryList::VAR_DIR,
                'application/pdf'
            );
        }

        $storeId = $orderPayment->getOrder()->getStoreId();

        $response = $this->client->getBoletoPdf($ourNumber, $storeId);

        $responseAsObject = json_decode($response->getBody()->getContents());

        $base64Pdf = $responseAsObject->pdf;

        $orderPayment->setAdditionalInformation('pdf_as_base64', $base64Pdf);

        $this->orderPaymentResource->save($orderPayment);

        return $this->fileFactory->create(
            $ourNumber . '.pdf',
            base64_decode($base64Pdf,true),
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }


}
