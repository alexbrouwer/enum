<?php

namespace PAR\Enum\Exception;

use PAR\Enum\Enumerable;

final class CloneNotSupportedException extends BadMethodCallException
{
    public static function for(Enumerable $enumerable): self
    {
        return new self(sprintf('Clone is not supported for enum %s', get_class($enumerable)));
    }
}
