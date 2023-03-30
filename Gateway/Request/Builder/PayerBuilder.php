<?php

namespace Mbissonho\BancoInter\Gateway\Request\Builder;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Mbissonho\BancoInter\Logger\LoggerInterface;
use Mbissonho\BancoInter\Model\Fields\AbstractOnlyNumbersField;
use Mbissonho\BancoInter\Model\Fields\CEP;
use Mbissonho\BancoInter\Model\Fields\CNPJ;
use Mbissonho\BancoInter\Model\Fields\CPF;

class PayerBuilder extends AbstractBuilder
{
    private CustomerRepositoryInterface $customerRepository;

    public function __construct(
        LoggerInterface $logger,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->customerRepository = $customerRepository;
        parent::__construct($logger);
    }

    public function build(array $buildSubject)
    {
        try {
            $paymentDataObject = SubjectReader::readPayment($buildSubject);

            $order = $paymentDataObject->getOrder();

            $billingAddress = $order->getBillingAddress();

            $customer = false;

            if ($order->getCustomerId()) {
                $customer = $this->customerRepository->getById($order->getCustomerId());
            }

            $taxvat = $customer && !is_null($customer->getTaxvat()) ? $customer->getTaxvat() :
                $paymentDataObject->getPayment()->getAdditionalInformation('customer_taxvat');

            $personType = $this->resolvePersonTypeByTaxvat($taxvat);

            if (!$personType) {
                throw new LocalizedException(__('Cannot determinate customer person type'));
            }

            return [
                'payer' => [
                    'person_type' => $personType,
                    'taxvat' => AbstractOnlyNumbersField::parseToOnlyNumbers($taxvat),
                    'name' => $billingAddress->getFirstname() . ' ' . $billingAddress->getLastname(),
                    'address_street' => $billingAddress->getStreetLine1(),
                    'address_city' => $billingAddress->getCity(),
                    'address_region_code' => $billingAddress->getRegionCode(),
                    'address_postcode' => CEP::parseToOnlyNumbers($billingAddress->getPostcode())
                ]
            ];
        } catch (\Throwable $e) {
            $this->logger->critical($e);
            throw new LocalizedException(__('Error processing payer information. Please verify address information and taxvat.'));
        }
    }

    private function resolvePersonTypeByTaxvat($taxvat)
    {
        return CPF::isValid($taxvat) ? 'FISICA' :
            (CNPJ::isValid($taxvat) ? 'JURIDICA' : false) ;
    }
}
