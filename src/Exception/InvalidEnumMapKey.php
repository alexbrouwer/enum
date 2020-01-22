<?php

declare(strict_types=1);

namespace PAR\Enum\Exception;

use PAR\Enum\Enumerable;

final class InvalidEnumMapKey extends InvalidArgumentException
{

    public static function withKey(Enumerable $key, string $expectedKeyType): self
    {
        return new self(
            sprintf(
                'Expected an instance of %s, got %s',
                $expectedKeyType,
                get_class($key)
            )
        );
    }
}
