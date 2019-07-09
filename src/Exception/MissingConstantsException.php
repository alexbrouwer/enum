<?php declare(strict_types=1);

namespace PAR\Enum\Exception;

final class MissingConstantsException extends RuntimeException
{
    public function __construct(string $class, array $missing)
    {
        parent::__construct(
            sprintf('Enum %s is missing constants [%s]', $class, implode(', ', $missing))
        );
    }
}
