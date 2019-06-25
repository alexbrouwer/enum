<?php declare(strict_types=1);

namespace PAR\Enum\Exception;

use PAR\Enum\Enumerable;
use ReflectionException;

class LogicException extends \LogicException implements ExceptionInterface
{

    public static function mustBeAbstract(string $class): self
    {
        return new self(sprintf('Enum %s must be declared abstract.', $class));
    }

    public static function missingElementsInDocBlock(string $class, array $extraEnumeratedKeys): self
    {
        return new self(
            sprintf(
                'Enum %s is missing "@method static self ...()" declaration(s) for [%s] in class docComment.',
                $class,
                implode(', ', $extraEnumeratedKeys)
            )
        );
    }

    public static function missingKeysInEnumeration(string $class, array $missingKeysInEnumeration): self
    {
        return new self(
            sprintf(
                'Enum %s is missing key(s) [%s] in enumerate().',
                $class,
                implode(', ', $missingKeysInEnumeration)
            )
        );
    }

    public static function notAllowedInEnumerate(Enumerable $enumerable, string $method): self
    {
        $class = static::nonAnonymousClass(get_class($enumerable));

        return new self(sprintf('Cannot call %s() within %s::enumerate().', $method, $class));
    }

    private static function nonAnonymousClass(string $class): string
    {
        if (strpos($class, 'class@anonymous') === 0) {
            $parents = class_parents($class);
            if (count($parents)) {
                return reset($parents);
            }
        }

        return $class;
    }

    public static function invalidInstance(string $class, string $name, $instance): self
    {
        $resolvedType = gettype($instance);
        if ('object' === $resolvedType) {
            $resolvedType = 'an instance of ' . static::nonAnonymousClass(get_class($instance));
        }

        return new self(
            sprintf(
                'Key "%s" in in %s::enumerate() must contain an instance of %s, got %s.',
                $name,
                $class,
                $class,
                $resolvedType
            )
        );
    }

    public static function invalidName(string $class, string $name, string $pattern): self
    {
        return new self(
            sprintf(
                'Element name "%s" in %s does not match pattern %s.',
                $name,
                $class,
                $pattern
            )
        );
    }

    public static function reflectionException(string $class, ReflectionException $e): self
    {
        return new self(sprintf('Unable to reflect on class %s', $class), 0, $e);
    }
}
