<?php

namespace PARTest\Enum\Fixtures;

use PAR\Enum\Enum;

/**
 * @method static self ELEMENT_A
 * @method static self ELEMENT_B
 */
abstract class WrongElementInstanceInEnumerateEnum extends Enum
{

    protected static function enumerate(): array
    {
        return [
            'ELEMENT_A' => new class()
                extends WrongElementInstanceInEnumerateEnum
            {

            },
            'ELEMENT_B' => ValidEnum::ELEMENT_B(),
        ];
    }
}
