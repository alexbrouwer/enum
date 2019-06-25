<?php

namespace PARTest\Enum\Fixtures;

use PAR\Enum\Enum;

/**
 * @method static self ELEMENT_A
 * @method static self ELEMENT_B
 */
abstract class MissingElementInDocBlockEnum extends Enum
{

    protected static function enumerate(): array
    {
        return [
            'UNDEFINED_ELEMENT' => new class() extends MissingElementInEnumerateEnum
            {

            },
        ];
    }
}
