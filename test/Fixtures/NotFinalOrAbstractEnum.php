<?php

declare(strict_types=1);

namespace PARTest\Enum\Fixtures;

use PAR\Enum\Enum;

/**
 * @method static self ELEMENT_ONE();
 * @method static self ELEMENT_TWO();
 */
class NotFinalOrAbstractEnum extends Enum
{

}
