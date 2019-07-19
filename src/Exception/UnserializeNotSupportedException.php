<?php declare(strict_types=1);

namespace PAR\Enum\Exception;

final class UnserializeNotSupportedException extends BadMethodCallException
{
    public static function for($object): self
    {
        return new self(sprintf('Unserialize is not supported for %s', get_class($object)));
    }
}
