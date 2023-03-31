<?php

namespace Mbissonho\BancoInter\Gateway\Boleto\Http\Client;

use GuzzleHttp\Exception\ClientException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Mbissonho\BancoInter\Logger\LoggerInterface;
use Mbissonho\BancoInter\Model\Api\Client;
use Mbissonho\BancoInter\Model\Ui\Boleto\ConfigProvider;

class CancelBoletoClient implements ClientInterface
{

    protected Client $client;

    protected OrderRepositoryInterface $orderRepository;

    protected ConfigProvider $boletoConfigProvider;

    protected LoggerInterface $logger;

    public function __construct(
        Client $client,
        OrderRepositoryInterface $orderRepository,
        ConfigProvider $boletoConfigProvider,
        LoggerInterface $logger
    )
    {
        $this->client = $client;
        $this->orderRepository = $orderRepository;
        $this->boletoConfigProvider = $boletoConfigProvider;
        $this->logger = $logger;
    }

    public function placeRequest(TransferInterface $transferObject)
    {
        try {
            $requestData = $transferObject->getBody();

            if(!$this->boletoConfigProvider->shouldCancelPaymentWhenCancelOrder($requestData['store_id'])) {
                return [
                    'status_code' => 200
                ];
            }

            $order = $this->orderRepository->get($requestData['order_id']);

            $response = $this->client->cancelPayment(
                $order->getPayment()->getAdditionalInformation('our_number'),
                $order->getStoreId()
            );

            return [
                'status_code' => $response->getStatusCode()
            ];

        } catch (ClientException |\Throwable $e) {
            $this->logger->critical($e);

            throw new LocalizedException(__('Something went wrong when trying to cancel the payment associated with the order'));
        }
    }
}
