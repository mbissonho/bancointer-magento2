<?php

namespace Mbissonho\BancoInter\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Mbissonho\BancoInter\Model\Fields\CNPJ;
use Mbissonho\BancoInter\Model\Fields\CPF;

class CpfCnpjValidator extends AbstractValidator
{

    public function validate(array $validationSubject): ResultInterface
    {
        $payment = $validationSubject['payment'] ?? null;

        if (!$payment) {
            return $this->createResult(false, [__('Can\'t get the payment information')]);
        }

        $additionalInformation = $payment->getAdditionalInformation();

        if(!isset($additionalInformation['customer_taxvat'])) {
            return $this->createResult(false, [__('Taxvat is invalid')]);
        }

        $value = $additionalInformation['customer_taxvat'];

        if(CPF::isValid($value) || CNPJ::isValid($value)) {
            return $this->createResult(true);
        }

        return $this->createResult(false, [__('Taxvat is invalid')]);
    }
}
