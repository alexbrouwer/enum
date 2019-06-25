<?php declare(strict_types=1);

namespace PAR\Enum\Exception;

class BadMethodCallException extends \BadMethodCallException implements ExceptionInterface
{

    public static function undefinedMethod(string $class, string $methodName): self
    {
        return new self(
            sprintf(
                'Call to undefined method %s::%s',
                $class,
                $methodName
            )
        );
    }
}
