<?php

namespace Mbissonho\BancoInter\Model\Fields;

abstract class AbstractOnlyNumbersField
{
    //TODO: Verificar a possibilidade de usar biblioteca externa
    public static function parseToOnlyNumbers($cnpj)
    {
        return preg_replace('/[^0-9]/', '', (string) $cnpj);
    }
}
