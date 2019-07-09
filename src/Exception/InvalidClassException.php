<?php declare(strict_types=1);

namespace PAR\Enum\Exception;

use Throwable;

final class InvalidClassException extends RuntimeException
{
    public function __construct(string $class, Throwable $prev = null)
    {
        parent::__construct(
            sprintf('Enum class %s must be declared abstract of final', $class),
            0,
            $prev
        );
    }
}
