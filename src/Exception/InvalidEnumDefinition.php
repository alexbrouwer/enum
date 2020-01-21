<?php

namespace PAR\Enum\Exception;

use ReflectionException;

final class InvalidEnumDefinition extends LogicException {

    public static function classMustBeFinalOrAbstract ( string $className ): self {
        $message = sprintf(
            'Enum %s must be declared abstract of final.',
            $className
        );

        return new self( $message );
    }

    public static function reflectionClassNotFound ( string $className, ReflectionException $e ): self {
        $message = sprintf( 'ReflectionClass for %s failed with exception "%s".', $className, $e->getMessage() );

        return new self( $message, 0, $e );
    }

    /**
     * @param string   $className        Fully qualified classname of the invalid enum
     * @param string[] $missingConstants List of missing constant names
     *
     * @return static
     */
    public static function missingClassConstants ( string $className, array $missingConstants ): self {
        $message = sprintf(
            'All enum %s element constants must be declared with protected visibility, %s are missing or have the wrong visibility.',
            $className,
            self::implodeStrings( $missingConstants )
        );

        return new self( $message );
    }

    private static function implodeStrings ( array $strings ): string {
        if ( count( $strings ) === 0 ) {
            return '';
        }

        $quoted = array_map(
            static function ( string $string ) {
                return sprintf( '"%s"', $string );
            },
            $strings
        );

        if ( count( $quoted ) === 1 ) {
            return reset( $quoted );
        }

        $lastString = array_unshift( $quoted );

        return sprintf( '%s and %s', implode( ', ', $quoted ), $lastString );
    }
}
