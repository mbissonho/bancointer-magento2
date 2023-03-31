<?php

namespace Mbissonho\BancoInter\Gateway\Boleto\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ValidatorInterface;

class IssueResponseValidator extends AbstractValidator implements ValidatorInterface
{

    public function validate(array $validationSubject): ResultInterface
    {
        $isValid = true;
        $errorsCode = [];

        if(!isset($validationSubject['response']['status_code'])) {
            return $this->createResult(false, [__('API Inter: Unexpected error')]);
        }

        $statusCode = $validationSubject['response']['status_code'];

        if($statusCode === 400) {
            $isValid = false;
            $errorsCode[] = 400;
        }

        if($statusCode === 403 || $statusCode === 401) {
            $isValid = false;
            $errorsCode[] = 403;
        }

        if($statusCode === 404) {
            $isValid = false;
            $errorsCode[] = 404;
        }

        if($statusCode === 503) {
            $isValid = false;
            $errorsCode[] = 503;
        }

        return $this->createResult($isValid, [], $errorsCode);
    }
}
