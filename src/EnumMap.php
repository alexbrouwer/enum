<?php declare(strict_types=1);

namespace PAR\Enum;

use IteratorAggregate;
use PAR\Core\ObjectInterface;
use PAR\Enum\Exception\InvalidEnumMapDefinition;
use PAR\Enum\Exception\InvalidEnumMapKey;
use PAR\Enum\Exception\InvalidEnumMapValue;
use Serializable;

final class EnumMap implements ObjectInterface, IteratorAggregate, Serializable {

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
    private static $supportedValueTypes = [
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
     * Array representation of this map. The nth element is the value to which universe[n] is currently mapped, or null
     * if it isn't mapped to anything, or NullValue if it's mapped to null.
     *
     * @var array<int, Enumerable|NullValue|null>
     */
    private $values;

    /**
     * @var int
     */
    private $size = 0;

    /**
     * Create a new EnumMap for specified enum
     *
     * @param string $keyType         Classname implementing Enumerable interface
     * @param string $valueType       Value type for values
     * @param bool   $allowNullValues True to allow null values
     *
     * @return static
     */
    public static function for ( string $keyType, string $valueType, bool $allowNullValues ): self {
        return new self( $keyType, $valueType, $allowNullValues );
    }

    /**
     * Create a new EnumMap for specified enum with a default boolean value
     *
     * @param string $keyType      Classname implementing Enumerable interface
     * @param bool   $defaultState Default state to set for each element
     *
     * @return static
     */
    public static function withState ( string $keyType, bool $defaultState ): self {
        $map = static::for( $keyType, static::TYPE_BOOL, false );

        $callable = [ $keyType, 'values' ];
        foreach ( $callable() as $key ) {
            $map->put( $key, $defaultState );
        }

        return $map;
    }

    /**
     * Removes all mappings from this map.
     */
    public function clear (): void {
        $this->values = array_fill( 0, count( $this->keyUniverse ), null );
        $this->size = 0;
    }

    /**
     * Assert if key is mapped in map.
     *
     * @param Enumerable $key The key to look for
     *
     * @return bool
     * @throws InvalidEnumMapKey If an invalid key type is provided
     */
    public function containsKey ( Enumerable $key ): bool {
        return $this->isValidKey( $key ) && null !== $this->values[ $key->ordinal() ];
    }

    /**
     * Assert if value is present in map
     *
     * @param mixed $value The value to find
     *
     * @return bool
     */
    public function containsValue ( $value ): bool {
        return in_array( $this->maskNull( $value ), $this->values, true );
    }

    /**
     * @inheritDoc
     */
    public function equals ( $other ): bool {
        if ( $this === $other ) {
            return true;
        }

        if ( $other instanceof self ) {
            return $this->keyType === $other->keyType
                   && $this->valueType === $other->valueType
                   && $this->allowNullValues === $other->allowNullValues
                   && $this->size === $other->size
                   && $this->values === $other->values;
        }

        return false;
    }

    /**
     * Returns the value to which the specified key is mapped, or null if this map contains no mapping for the key.
     *
     * @param Enumerable $key The key to retrieve a value for
     *
     * @return mixed
     * @throws InvalidEnumMapKey If an invalid key type is provided
     */
    public function get ( Enumerable $key ) {
        $this->assertKeyType( $key );

        return $this->unmaskNull( $this->values[ $key->ordinal() ] );
    }

    /**
     * @inheritDoc
     */
    public function getIterator () {
        foreach ( $this->keyUniverse as $key ) {
            if ( null === $this->values[ $key->ordinal() ] ) {
                continue;
            }
            yield $key => $this->unmaskNull( $this->values[ $key->ordinal() ] );
        }
    }

    /**
     * @param Enumerable $key   The key to change
     * @param mixed|null $value The value to set
     *
     * @return mixed The previous value associated with the provided key, or null if there was no value for it.
     * @throws InvalidEnumMapKey When provided key is not allowed
     * @throws InvalidEnumMapValue When provided value is not allowed
     */
    public function put ( Enumerable $key, $value ) {
        $this->assertKeyType( $key );
        $this->assertValueType( $value, $key );

        $index = $key->ordinal();
        $oldValue = $this->values[ $index ];

        $this->values[ $index ] = $this->maskNull( $value );

        if ( null === $oldValue ) {
            ++$this->size;
        }

        return $this->unmaskNull( $oldValue );
    }

    /**
     * Removes the mapping for this key from this map if present.
     *
     * @param Enumerable $key The key to remove
     *
     * @return mixed The previous value associated with the specified key, or null if there was no mapping for the key.
     * @throws InvalidEnumMapKey If an invalid key type is provided
     */
    public function remove ( Enumerable $key ) {
        $this->assertKeyType( $key );

        $index = $key->ordinal();
        $oldValue = $this->values[ $index ];
        $this->values[ $index ] = null;
        if ( null !== $oldValue ) {
            --$this->size;
        }

        return $this->unmaskNull( $oldValue );
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
                'keyType' => $this->keyType,
                'valueType' => $this->valueType,
                'allowNullValues' => $this->allowNullValues,
                'values' => $values,
            ]
        );
    }

    /**
     * Returns the number of key-value mappings in this map
     */
    public function size (): int {
        return $this->size;
    }

    /**
     * @inheritDoc
     */
    public function toString (): string {
        return sprintf(
            '<%s,%s%s>',
            $this->keyType,
            $this->allowNullValues ? '?' : '',
            $this->valueType
        );
    }

    /**
     * @inheritDoc
     */
    public function unserialize ( $serialized ): void {
        $data = unserialize( $serialized, [ 'allowed_classes' => true ] );
        $this->__construct( $data[ 'keyType' ], $data[ 'valueType' ], $data[ 'allowNullValues' ] );
        foreach ( $this->keyUniverse as $key ) {
            if ( array_key_exists( $key->ordinal(), $data[ 'values' ] ) ) {
                $this->put( $key, $data[ 'values' ][ $key->ordinal() ] );
            }
        }
    }

    /**
     * Returns the values contained in this map.
     *
     * The array will contain the values in the order their corresponding keys appear in the map, which is their natural
     * order (the order in which the enum constants are declared).
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
                    static function ( $value ): bool {
                        return null !== $value;
                    }
                )
            )
        );
    }

    private static function null (): object {
        if ( !self::$null ) {
            self::$null = new class() implements NullValue {

            };
        }

        return self::$null;
    }

    private function assertKeyType ( Enumerable $key ) {
        if ( !$this->isValidKey( $key ) ) {
            throw InvalidEnumMapKey::withKey( $key, $this->keyType );
        }
    }

    /**
     * @param Enumerable $key
     *
     * @return bool
     */
    private function isValidKey ( Enumerable $key ): bool {
        return get_class( $key ) === $this->keyType;
    }

    private function assertValueType ( $value, Enumerable $key ) {
        if ( null === $value ) {
            if ( !$this->allowNullValues ) {
                throw InvalidEnumMapValue::nullNotAllowedFor( $key );
            }

            return;
        }

        switch ( $this->valueType ) {
            case self::TYPE_MIXED:
                $test = static function () {
                    return true;
                };
                break;
            case self::TYPE_BOOL:
            case self::TYPE_BOOLEAN:
                $test = static function ( $value ) {
                    return is_bool( $value );
                };
                break;
            case self::TYPE_INT:
            case self::TYPE_INTEGER:
                $test = static function ( $value ) {
                    return is_int( $value );
                };
                break;
            case self::TYPE_FLOAT:
            case self::TYPE_DOUBLE:
                $test = static function ( $value ) {
                    return is_float( $value );
                };
                break;
            case self::TYPE_STRING:
                $test = static function ( $value ) {
                    return is_string( $value );
                };
                break;
            case self::TYPE_OBJECT:
                $test = static function ( $value ) {
                    return is_object( $value );
                };
                break;
            case self::TYPE_ARRAY:
                $test = static function ( $value ) {
                    return is_array( $value );
                };
                break;
            case self::TYPE_CALLABLE:
                $test = static function ( $value ) {
                    return is_callable( $value );
                };
                break;
            default:
                $test = static function ( $value, $type ) {
                    return $value instanceof $type;
                };
                break;
        }

        if ( !$test( $value, $this->valueType ) ) {
            throw InvalidEnumMapValue::wrongTypeFor( $key, $value, $this->valueType );
        }
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
    private function unmaskNull ( $value ) {
        $null = self::null();
        if ( $value instanceof $null ) {
            return null;
        }

        return $value;
    }

    private function __construct ( string $keyType, string $valueType, bool $allowNullValues ) {
        if ( !class_exists( $keyType ) || !in_array( Enumerable::class, class_implements( $keyType, true ), true ) ) {
            throw InvalidEnumMapDefinition::withInvalidKeyType( $keyType );
        }
        $this->keyType = $keyType;

        $this->valueType = $valueType;
        if ( !in_array( $valueType, self::$supportedValueTypes, true ) && !class_exists( $valueType ) ) {
            throw InvalidEnumMapDefinition::withInvalidValueType( $valueType, self::$supportedValueTypes );
        }

        $this->allowNullValues = $allowNullValues;

        $callable = [ $keyType, 'values' ];
        $this->keyUniverse = $callable();
        $this->values = array_fill( 0, count( $this->keyUniverse ), null );
    }
}
