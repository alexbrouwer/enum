<?php

namespace PARTest\Enum\Fixtures;

use PAR\Enum\Enum;

/**
 * @method static self ELEMENT_A
 */
abstract class OrdinalWithinEnumerateEnum extends Enum
{

    protected static function enumerate(): array
    {
        $obj = new class extends OrdinalWithinEnumerateEnum
        {

        };
        $obj->ordinal();

        return [
            'ELEMENT_A' => $obj,
        ];
    }
}
