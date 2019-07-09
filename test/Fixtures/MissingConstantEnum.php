<?php

namespace PARTest\Enum\Fixtures;

use PAR\Enum\Enum;

/**
 * @method static self LEFT()
 * @method static self RIGHT()
 */
final class MissingConstantEnum extends Enum
{
    protected const LEFT = [];
}
