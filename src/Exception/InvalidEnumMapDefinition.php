<?php

namespace PAR\Enum\Exception;

use PAR\Enum\Enumerable;

final class InvalidEnumMapDefinition extends InvalidArgumentException {

    public static function withInvalidKeyType ( string $keyType ): self {
        return new self(
            sprintf(
                'Invalid keyType, must be a fully qualified class name implementing %s, got "%s"',
                Enumerable::class,
                $keyType
            )
        );
    }

    public static function withInvalidValueType ( string $valueType, array $supportedValueTypes ): self {
        return new self(
            sprintf(
                'Invalid valueType, expected one of [%s] or an existing class, got "%s"',
                implode( ',', $supportedValueTypes ),
                $valueType
            )
        );
    }
}
