<?php

namespace PAR\Enum;

use IteratorAggregate;
use PAR\Core\ObjectInterface;
use PAR\Enum\Exception\InvalidArgumentException;
use Serializable;
use Traversable;

final class EnumMap implements ObjectInterface, IteratorAggregate, Serializable
{
    const TYPE_MIXED = 'mixed';
    const TYPE_BOOL = 'bool';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_INT = 'int';
    const TYPE_INTEGER = 'integer';
    const TYPE_FLOAT = 'float';
    const TYPE_DOUBLE = 'double';
    const TYPE_STRING = 'string';
    const TYPE_OBJECT = 'object';
    const TYPE_ARRAY = 'array';
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
    ];

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
     * @var array<int, Enum>
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

    public static function for(string $keyType, string $valueType, bool $allowNullValues): self
    {
        return new self($keyType, $valueType, $allowNullValues);
    }

    /**
     * @param string $keyType
     * @param string $valueType
     * @param bool   $allowNullValues
     */
    public function __construct(string $keyType, string $valueType, bool $allowNullValues)
    {
        if (!is_subclass_of($keyType, Enum::class)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Class %s does not extend %s',
                    $keyType,
                    Enum::class
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
     * @param Enum  $key   The key to change
     * @param mixed $value The value to set
     *
     * @return mixed The previous value associated with the specified key, or null if there was no mapping for the key.
     * @throws InvalidArgumentException If the passed key does not match the key type
     * @throws InvalidArgumentException If the passed value does not match the value type
     */
    public function put(Enum $key, $value)
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
     * Assert if key is mapped in map.
     *
     * @param Enum $key
     *
     * @return bool
     * @throws InvalidArgumentException If an invalid key type is provided
     */
    public function containsKey(Enum $key): bool
    {
        $this->checkKeyType($key);

        return null !== $this->values[$key->ordinal()];
    }

    /**
     * Returns the value to which the specified key is mapped, or null if this map contains no mapping for the key.
     *
     * @param Enum $key The key to retrieve a value for
     *
     * @return mixed
     * @throws InvalidArgumentException If an invalid key type is provided
     */
    public function get(Enum $key)
    {
        $this->checkKeyType($key);

        return $this->unmaskNull($this->values[$key->ordinal()]);
    }

    /**
     * Removes the mapping for this key from this map if present.
     *
     * @param Enum $key The key to remove
     *
     * @return mixed The previous value associated with the specified key, or null if there was no mapping for the key.
     */
    public function remove(Enum $key)
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
     * Returns the values contained in this map.
     *
     * The array will contain the values in the order their corresponding keys appear in the map, which is their natural
     * order (the order in which the num constants are declared).
     *
     * @return array
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
     * Removes all mappings from this map.
     */
    public function clear(): void
    {
        $this->values = array_fill(0, count($this->keyUniverse), null);
        $this->size = 0;
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

    private function checkKeyType(Enum $key): void
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
            return NullValue::instance();
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
        if ($value instanceof NullValue) {
            return null;
        }

        return $value;
    }
}
