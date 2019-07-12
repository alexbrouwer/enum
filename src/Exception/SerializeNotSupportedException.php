<?php

namespace PAR\Enum\Exception;

final class SerializeNotSupportedException extends BadMethodCallException
{
    public static function for(object $object): self
    {
        return new self(sprintf('Unserialize is not supported for %s', get_class($object)));
    }
}
