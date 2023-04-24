<?php

namespace Mbissonho\BancoInter\Gateway\Boleto\Http\Client;

use GuzzleHttp\Exception\ClientException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Mbissonho\BancoInter\Logger\Boleto\Logger;
use Mbissonho\BancoInter\Model\Api\Client;

class IssueBoletoClient implements ClientInterface
{
    protected Logger $logger;

    protected Client $apiClient;

    public function __construct(
        Logger $logger,
        Client $apiClient
    )
    {
        $this->logger = $logger;
        $this->apiClient = $apiClient;
    }

    public function placeRequest(TransferInterface $transferObject): array
    {
        try {
            $requestData = $transferObject->getBody();

            $requestBody = [
                'seuNumero' => $requestData['order_increment_id'],
                'valorNominal' => $requestData['transaction']['amount'],
                'dataVencimento' => $requestData['transaction']['expiration_date'],
                'numDiasAgenda' => $requestData['transaction']['days_to_cancel_after_expiration'],
                'pagador' => [
                    'tipoPessoa' => $requestData['payer']['person_type'],
                    'cpfCnpj' => $requestData['payer']['taxvat'],
                    'nome' => $requestData['payer']['name'],
                    'endereco' => $requestData['payer']['address_street'],
                    'cidade' => $requestData['payer']['address_city'],
                    'uf' => $requestData['payer']['address_region_code'],
                    'cep' => $requestData['payer']['address_postcode']
                ]
            ];

            $this->logger->debug(
                [
                    'ISSUE_BOLETO_REQUEST' => [
                        'BODY' => $requestBody
                    ]
                ]
            );

            $response = $this->apiClient->issueBoleto($requestBody);
            $responseBody = $response->getBody()->getContents();

            $this->logger->debug(
                [
                    'ISSUE_BOLETO_RESPONSE' => [
                        'STATUS_CODE' => $response->getStatusCode(),
                        'BODY' => $responseBody
                    ]
                ]
            );

            return [
                'status_code' => $response->getStatusCode(),
                'body' => $responseBody
            ];

        } catch (ClientException $e) {
            $this->logger->critical(
                [$e->getMessage()]
            );

            return [
                'status_code' => $e->getResponse()->getStatusCode(),
                'body' => $e->getResponse()->getBody()->getContents()
            ];

        } catch (\Throwable $e) {
            $this->logger->critical(
                [$e->getMessage()]
            );
            //TODO: Add client related exception message
            throw new LocalizedException(__('Something went wrong. Check log file.'));
        }
    }


}
