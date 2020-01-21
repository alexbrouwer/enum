<?php

namespace PAR\Enum\Exception;

use PAR\Enum\Enumerable;

final class InvalidEnumMapValue extends InvalidArgumentException {

    public static function nullNotAllowedFor ( Enumerable $key ): self {
        return new self(
            sprintf(
                'Null not allowed, got null for key %s',
                $key->toString()
            )
        );
    }

    public static function wrongTypeFor ( Enumerable $key, $value, string $expectedType ): self {
        return new self(
            sprintf(
                'Expected a value of type %s for key %s, got %s',
                $expectedType,
                $key->toString(),
                is_object( $value ) ? get_class( $value ) : gettype( $value )
            )
        );
    }
}
