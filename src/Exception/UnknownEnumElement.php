<?php

declare(strict_types=1);

namespace PAR\Enum\Exception;

final class UnknownEnumElement extends InvalidArgumentException
{

    public static function withName(string $className, string $elementName): self
    {
        $message = sprintf('Unknown enum element %s::%s', $className, $elementName);

        return new self($message);
    }
}
