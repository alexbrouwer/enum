<?php

namespace PARTest\Enum\Fixtures;

use PAR\Enum\Enum;

/**
 * @method static self ELEMENT_A
 */
abstract class NameWithinEnumerateEnum extends Enum
{

    protected static function enumerate(): array
    {
        $obj = new class extends NameWithinEnumerateEnum
        {

        };
        $obj->name();

        return [
            'ELEMENT_A' => $obj,
        ];
    }
}
