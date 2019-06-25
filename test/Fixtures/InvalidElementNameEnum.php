<?php

namespace PARTest\Enum\Fixtures;

use PAR\Enum\Enum;
use PAR\Enum\Exception\InvalidArgumentException;

/**
 * @method static self INVA LID()
 */
abstract class InvalidElementNameEnum extends Enum
{

    protected static function enumerate(): array
    {
        return [
            'INVA LID' => new class() extends InvalidArgumentException
            {

            },
        ];
    }
}
