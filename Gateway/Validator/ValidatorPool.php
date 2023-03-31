<?php

namespace Mbissonho\BancoInter\Gateway\Validator;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\ObjectManager\TMapFactory;
use Magento\Payment\Gateway\Validator\ValidatorInterface;

class ValidatorPool
{

    /**
     * @var ValidatorInterface[]
     */
    private $validators;

    /**
     * @param TMapFactory $tmapFactory
     * @param array $validators
     */
    public function __construct(
        TMapFactory $tmapFactory,
        array $validators = []
    ) {
        $this->validators = $tmapFactory->create(
            [
                'array' => $validators,
                'type' => ValidatorInterface::class
            ]
        );
    }

    /**
     * Returns configured validator
     *
     * @param string $code
     * @return ValidatorInterface
     * @throws NotFoundException
     */
    public function get($code)
    {
        if (!isset($this->validators[$code])) {
            throw new NotFoundException(__('The validator for the "%1" field doesn\'t exist.', $code));
        }

        return $this->validators[$code];
    }

    /**
     * Returns all validators
     *
     * @throws \Exception
     */
    public function all(): \ArrayIterator
    {
        return $this->validators->getIterator();
    }

}
