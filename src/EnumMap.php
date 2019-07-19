<?php declare(strict_types=1);

namespace PAR\Enum;

use IteratorAggregate;
use PAR\Core\ObjectCastToString;
use PAR\Core\ObjectInterface;
use PAR\Enum\Exception\InvalidArgumentException;
use Serializable;
use Traversable;

final class EnumMap implements ObjectInterface, IteratorAggregate, Serializable
{
    use ObjectCastToString;

    private const TYPE_MIXED = 'mixed';
    private const TYPE_BOOL = 'bool';
    private const TYPE_BOOLEAN = 'boolean';
    private const TYPE_INT = 'int';
    private const TYPE_INTEGER = 'integer';
    private const TYPE_FLOAT = 'float';
    private const TYPE_DOUBLE = 'double';
    private const TYPE_STRING = 'string';
    private const TYPE_OBJECT = 'object';
    private const TYPE_ARRAY = 'array';
    private const TYPE_CALLABLE = 'callable';

    /**
     * @var array<string>
     */
    private static $valueTypes = [
        self::TYPE_MIXED,
        self::TYPE_BOOL,
        self::TYPE_BOOLEAN,
        self::TYPE_INT,
        self::TYPE_INTEGER,
        self::TYPE_FLOAT,
        self::TYPE_DOUBLE,
        self::TYPE_STRING,
        self::TYPE_OBJECT,
        self::TYPE_ARRAY,
        self::TYPE_CALLABLE,
    ];
    /**
     * @var null|NullValue
     */
    private static $null;

    /**
     * The class name of the key
     *
     * @var string
     */
    private $keyType;

    /**
     * The type of the value
     *
     * @var string
     */
    private $valueType;

    /**
     * @var bool
     */
    private $allowNullValues;

    /**
     * All of the possible keys, cached for performance
     *
     * @var array<int, Enumerable>
     */
    private $keyUniverse;

    /**
     * Array representation of this map. The ith element is the value to which universe[i] is currently mapped, or null
     * if it isn't mapped to anything, or NullValue if it's mapped to null.
     *
     * @var array<int, mixed>
     */
    private $values;

    /**
     * @var int
     */
    private $size = 0;

    /**
     * @param string $keyType         FQCN implementing Enumerable
     * @param string $valueType       Value type for values
     * @param bool   $allowNullValues True to allow NULL
     *
     * @return EnumMap
     */
    public static function for(string $keyType, string $valueType, bool $allowNullValues): self
    {
        return new self($keyType, $valueType, $allowNullValues);
    }

    /**
     * Removes all mappings from this map.
     */
    public function clear(): void
    {
        $this->values = array_fill(0, count($this->keyUniverse), null);
        $this->size = 0;
    }

    /**
     * Assert if key is mapped in map.
     *
     * @param Enumerable $key
     *
     * @return bool
     * @throws InvalidArgumentException If an invalid key type is provided
     */
    public function containsKey(Enumerable $key): bool
    {
        $this->checkKeyType($key);

        return null !== $this->values[$key->ordinal()];
    }

    /**
     * Assert if value is present in map
     *
     * @param mixed $value The value to find
     *
     * @return bool
     */
    public function containsValue($value): bool
    {
        return in_array($this->maskNull($value), $this->values, true);
    }

    /**
     * @inheritDoc
     */
    public function equals($other): bool
    {
        if ($this === $other) {
            return true;
        }

        if ($other instanceof self && get_class($other) === static::class) {
            return $this->size === $other->size && $this->values === $other->values;
        }

        return false;
    }

    /**
     * Returns the value to which the specified key is mapped, or null if this map contains no mapping for the key.
     *
     * @param Enumerable $key The key to retrieve a value for
     *
     * @return mixed
     * @throws InvalidArgumentException If an invalid key type is provided
     */
    public function get(Enumerable $key)
    {
        $this->checkKeyType($key);

        return $this->unmaskNull($this->values[$key->ordinal()]);
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): Traversable
    {
        foreach ($this->keyUniverse as $key) {
            if (null === $this->values[$key->ordinal()]) {
                continue;
            }

            yield $key => $this->unmaskNull($this->values[$key->ordinal()]);
        }
    }

    /**
     * @param Enumerable $key   The key to change
     * @param mixed      $value The value to set
     *
     * @return mixed The previous value associated with the specified key, or null if there was no mapping for the key.
     * @throws InvalidArgumentException If the passed key does not match the key type
     * @throws InvalidArgumentException If the passed value does not match the value type
     */
    public function put(Enumerable $key, $value)
    {
        $this->checkKeyType($key);

        if (!$this->isValidValue($value)) {
            throw new InvalidArgumentException(sprintf('Expected a value of type %s, got %s', $this->valueType, gettype($value)));
        }

        $index = $key->ordinal();
        $oldValue = $this->values[$index];

        $this->values[$index] = $this->maskNull($value);

        if (null === $oldValue) {
            ++$this->size;
        }

        return $this->unmaskNull($oldValue);
    }

    /**
     * Removes the mapping for this key from this map if present.
     *
     * @param Enumerable $key The key to remove
     *
     * @return mixed The previous value associated with the specified key, or null if there was no mapping for the key.
     */
    public function remove(Enumerable $key)
    {
        $this->checkKeyType($key);
        $index = $key->ordinal();
        $oldValue = $this->values[$index];
        $this->values[$index] = null;
        if (null !== $oldValue) {
            --$this->size;
        }

        return $this->unmaskNull($oldValue);
    }

    /**
     * @inheritDoc
     */
    public function serialize(): string
    {
        $values = [];
        foreach ($this->values as $ordinal => $value) {
            if (null === $value) {
                continue;
            }
            $values[$ordinal] = $this->unmaskNull($value);
        }

        return serialize(
            [
                'keyType'         => $this->keyType,
                'valueType'       => $this->valueType,
                'allowNullValues' => $this->allowNullValues,
                'values'          => $values,
            ]
        );
    }

    /**
     * Returns the number of key-value mappings in this map
     *
     * @return int
     */
    public function size(): int
    {
        return $this->size;
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        return sprintf('<%s,%s%s>', $this->keyType, $this->allowNullValues ? '?' : '', $this->valueType);
    }

    /**
     * @inheritDoc
     */
    public function unserialize($serialized): void
    {
        $data = unserialize($serialized, ['allowed_classes' => true]);
        $this->__construct($data['keyType'], $data['valueType'], $data['allowNullValues']);
        foreach ($this->keyUniverse as $key) {
            if (array_key_exists($key->ordinal(), $data['values'])) {
                $this->put($key, $data['values'][$key->ordinal()]);
            }
        }
    }

    /**
     * Returns the values contained in this map.
     *
     * The array will contain the values in the order their corresponding keys appear in the map, which is their natural
     * order (the order in which the num constants are declared).
     *
     * @return array<int>
     */
    public function values(): array
    {
        return array_values(
            array_map(
                function ($value) {
                    return $this->unmaskNull($value);
                },
                array_filter(
                    $this->values,
                    static function ($value): bool {
                        return null !== $value;
                    }
                )
            )
        );
    }

    private static function null(): object
    {
        if (!self::$null) {
            self::$null = new class()
            {
            };
        }

        return self::$null;
    }

    private function checkKeyType(Enumerable $key): void
    {
        if (get_class($key) !== $this->keyType) {
            throw new InvalidArgumentException(
                sprintf(
                    'Object of type %s is not the same type as %s',
                    get_class($key),
                    $this->keyType
                )
            );
        }
    }

    private function isValidValue($value): bool
    {
        if (null === $value) {
            if ($this->allowNullValues) {
                return true;
            }

            return false;
        }

        switch ($this->valueType) {
            case self::TYPE_MIXED:
                return true;
            case self::TYPE_BOOL:
            case self::TYPE_BOOLEAN:
                return is_bool($value);
            case self::TYPE_INT:
            case self::TYPE_INTEGER:
                return is_int($value);
            case self::TYPE_FLOAT:
            case self::TYPE_DOUBLE:
                return is_float($value);
            case self::TYPE_STRING:
                return is_string($value);
            case self::TYPE_OBJECT:
                return is_object($value);
            case self::TYPE_ARRAY:
                return is_array($value);
            case self::TYPE_CALLABLE:
                return is_callable($value);
        }

        return $value instanceof $this->valueType;
    }

    /**
     * Converts null to NullValue, leaves other types as is
     *
     * @param mixed $value The value to mask
     *
     * @return mixed|NullValue
     */
    private function maskNull($value)
    {
        if (null === $value) {
            return self::null();
        }

        return $value;
    }

    /**
     * Converts NullValue to null, leaves other types as is
     *
     * @param mixed|NullValue $value
     *
     * @return mixed
     */
    private function unmaskNull($value)
    {
        $null = self::null();
        if ($value instanceof $null) {
            return null;
        }

        return $value;
    }

    /**
     * @param string $keyType
     * @param string $valueType
     * @param bool   $allowNullValues
     */
    private function __construct(string $keyType, string $valueType, bool $allowNullValues)
    {
        if (!in_array(Enumerable::class, class_implements($keyType), true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Class %s does not implement %s',
                    $keyType,
                    Enumerable::class
                )
            );
        }

        $this->keyType = $keyType;

        if (!in_array($valueType, self::$valueTypes, true) && !class_exists($valueType)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Unknown value type %s, expected one of [%s] or existing class',
                    $valueType,
                    implode(',', self::$valueTypes)
                )
            );
        }

        $this->valueType = $valueType;
        $this->allowNullValues = $allowNullValues;

        /** @var callable $callable */
        $callable = [$keyType, 'values'];
        $this->keyUniverse = $callable();
        $this->values = array_fill(0, count($this->keyUniverse), null);
    }
}
