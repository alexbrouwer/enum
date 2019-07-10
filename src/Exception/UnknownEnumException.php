<?php declare(strict_types=1);

namespace PAR\Enum\Exception;

use InvalidArgumentException;

final class UnknownEnumException extends InvalidArgumentException implements ExceptionInterface
{

    public static function withName(string $class, string $name): self
    {
        return new self(
            sprintf(
                'Unknown enum %s::%s',
                $class,
                $name
            )
        );
    }
}
