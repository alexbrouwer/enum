<?php

namespace PAR\Enum\Exception;

final class CloneNotSupportedException extends BadMethodCallException
{
    public static function for(object $object): self
    {
        return new self(sprintf('Clone is not supported for %s', get_class($object)));
    }
}
