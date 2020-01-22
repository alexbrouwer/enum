<?php

declare(strict_types=1);

namespace PARTest\Enum\Fixtures;

use PAR\Enum\Enum;

/**
 * @method static self PROTECTED_CONST();
 * @method static self PUBLIC_CONST();
 * @method static self PRIVATE_CONST();
 * @method static self MISSING_CONST();
 */
final class MissingElementConstantEnum extends Enum
{

    public const PUBLIC_CONST = [];
    protected const PROTECTED_CONST = [];
    private const PRIVATE_CONST = [];
}
