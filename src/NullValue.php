<?php declare(strict_types=1);

namespace PAR\Enum;

use PAR\Enum\Exception\CloneNotSupportedException;
use PAR\Enum\Exception\SerializeNotSupportedException;
use PAR\Enum\Exception\UnserializeNotSupportedException;

/**
 * @internal
 */
final class NullValue
{
    /**
     * @var self|null
     */
    private static $instance;

    public static function instance(): self
    {
        return self::$instance ?: self::$instance = new self();
    }

    private function __construct()
    {
    }

    /**
     *
     * @throws CloneNotSupportedException
     */
    public function __clone()
    {
        throw CloneNotSupportedException::for($this);
    }

    /**
     * @noinspection MagicMethodsValidityInspection
     * @throws SerializeNotSupportedException
     */
    public function __sleep(): void
    {
        throw SerializeNotSupportedException::for($this);
    }

    /**
     * @throws UnserializeNotSupportedException
     */
    public function __wakeup(): void
    {
        throw UnserializeNotSupportedException::for($this);
    }
}
