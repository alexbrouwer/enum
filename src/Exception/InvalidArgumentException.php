<?php declare(strict_types=1);

namespace PAR\Enum\Exception;

class InvalidArgumentException extends \InvalidArgumentException implements ExceptionInterface
{

    public static function unknownElement(string $class, string $name): self
    {
        return new self(sprintf('Enum %s does not have an element "%s".', $class, $name));
    }
}
