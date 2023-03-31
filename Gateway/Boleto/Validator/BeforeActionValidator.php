<?php

namespace Mbissonho\BancoInter\Gateway\Boleto\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Magento\Payment\Gateway\Validator\ValidatorInterface;
use Mbissonho\BancoInter\Gateway\Validator\ValidatorPool;

class BeforeActionValidator extends AbstractValidator implements ValidatorInterface
{

    private ValidatorPool $fieldsValidatorPool;

    public function __construct(
        ResultInterfaceFactory $resultFactory,
        ValidatorPool $fieldsValidatorPool
    )
    {
        $this->fieldsValidatorPool = $fieldsValidatorPool;
        parent::__construct($resultFactory);
    }


    public function validate(array $validationSubject): ResultInterface
    {
        $valid = true;
        $messages = [];

        $payment = $validationSubject['payment'] ?? null;

        if (!$payment) {
            return $this->createResult(false, [__('Can\'t get the payment information')]);
        }

        try {
            $validators = $this->fieldsValidatorPool->all();
        } catch (\Throwable $e) {
            return $this->createResult(false);
        }

        foreach ($validators as $validator) {
            /* @var ValidatorInterface $validator */

            $result = $validator->validate($validationSubject);

            if(!$result->isValid()) {
                $valid = false;
                $messages = array_merge($result->getFailsDescription(), $messages);
            }
        }

        return $this->createResult($valid, $messages);
    }
}
