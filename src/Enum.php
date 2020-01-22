<?php declare(strict_types=1);

namespace PAR\Enum;

use PAR\Core\ObjectInterface;
use PAR\Enum\Exception\InvalidEnumDefinition;
use PAR\Enum\Exception\UnknownEnumElement;
use ReflectionClass;
use ReflectionException;
use Serializable;

abstract class Enum implements Enumerable, ObjectInterface, Serializable {

    /**
     * @var array<string, array<int, array>>
     */
    private static $configuration = [];

    /**
     * @var array<string, bool>
     */
    private static $allInstancesLoaded = [];

    /**
     * @var array<string, array<string, static>>
     */
    private static $instances = [];

    /**
     * @var int
     */
    private $ordinal;

    /**
     * @var string
     */
    private $name;

    /**
     * When creating your own constructor for a parameterized enum, make sure to declare it as protected, so that the
     * static methods are able to construct it. Do not make it public, as that would allow creation of non-singleton
     * enum instances.
     */
    protected function __construct () {
        // Default implementation is empty
    }

    /**
     * Maps static methods calls to instances.
     *
     * @param string $name      The name of the instance.
     * @param array  $arguments Ignored.
     *
     * @return static
     * @throws InvalidEnumDefinition
     */
    final public static function __callStatic ( string $name, array $arguments ): self {
        return static::valueOf( $name );
    }

    /**
     * @inheritDoc
     */
    final public static function valueOf ( string $name ): self {
        if ( isset( self::$instances[ static::class ][ $name ] ) ) {
            return self::$instances[ static::class ][ $name ];
        }
        $configuration = self::configuration();
        if ( array_key_exists( $name, $configuration ) ) {
            [ $ordinal, $arguments ] = $configuration[ $name ];

            return self::createValue( $name, $ordinal, $arguments );
        }
        throw UnknownEnumElement::withName( static::class, $name );
    }

    private static function configuration (): array {
        $className = static::class;
        if ( isset( self::$configuration[ $className ] ) ) {
            return self::$configuration[ $className ];
        }

        self::$configuration[ $className ] = [];

        try {
            $reflectionClass = new ReflectionClass( $className );
        } catch ( ReflectionException $e ) {
            throw InvalidEnumDefinition::reflectionClassNotFound( $className, $e );
        }
        if ( !$reflectionClass->isAbstract() && !$reflectionClass->isFinal() ) {
            throw InvalidEnumDefinition::classMustBeFinalOrAbstract( $className );
        }

        $constants = [];
        foreach ( $reflectionClass->getReflectionConstants() as $reflectionClassConstant ) {
            if ( !$reflectionClassConstant->isProtected() ) {
                continue;
            }
            $value = $reflectionClassConstant->getValue();
            $constants[ $reflectionClassConstant->getName() ] = is_array( $value ) ? $value : [];
        }

        $methods = self::resolveMethodsFromDocBlock( $reflectionClass );

        // Validate all or none of the methods have a constant value
        $missingConstants = array_diff( $methods, array_keys( $constants ) );
        $numMissingConstants = count( $missingConstants );
        if ($numMissingConstants > 0 && $numMissingConstants !== count($methods)) {
            throw InvalidEnumDefinition::missingClassConstants( $className, $missingConstants );
        }

        $ordinal = -1;
        foreach ($methods as $methodName) {
            self::$configuration[static::class][$methodName] = [
                ++$ordinal,
                $constants[$methodName] ?? [],
            ];
        }

        return self::$configuration[static::class];
    }

    private static function resolveMethodsFromDocBlock(ReflectionClass $reflection): array
    {
        $values = [];
        $docComment = $reflection->getDocComment();
        if (!$docComment) {
            return $values;
        }

        preg_match_all( '/@method\s+static\s+self\s+([\w]+)\(\s*?\)/', $docComment, $matches );
        foreach ($matches[1] ?? [] as $value) {
            $values[] = $value;
        }

        return $values;
    }

    private static function createValue(string $name, int $ordinal, array $arguments): self
    {
        /**
         * The default implementation does not accept any arguments
         *
         * @noinspection PhpMethodParametersCountMismatchInspection
         */
        $instance = new static( ...$arguments );
        $instance->name = $name;
        $instance->ordinal = $ordinal;

        return self::$instances[ static::class ][ $name ] = $instance;
    }

    /**
     * @inheritDoc
     */
    final public static function values (): array {
        $className = static::class;
        if ( isset( self::$allInstancesLoaded[ $className ] ) ) {
            return array_values( self::$instances[ $className ] );
        }

        if ( !isset( self::$instances[ $className ] ) ) {
            self::$instances[ $className ] = [];
        }

        foreach ( self::configuration() as $name => $configuration ) {
            if ( array_key_exists( $name, self::$instances[ $className ] ) ) {
                continue;
            }

            [ $ordinal, $arguments ] = $configuration;

            static::createValue( $name, $ordinal, $arguments );
        }

        uasort(
            self::$instances[ $className ],
            static function ( self $a, self $b ) {
                return $a->ordinal() <=> $b->ordinal();
            }
        );

        self::$allInstancesLoaded[ $className ] = true;

        return array_values( self::$instances[ $className ] );
    }

    /**
     * @inheritDoc
     */
    final public function ordinal (): int {
        return $this->ordinal;
    }

    /**
     * @inheritDoc
     */
    final public function toString (): string {
        return $this->name();
    }

    /**
     * @inheritDoc
     */
    final public function name (): string {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function equals ( $other ): bool {
        if ( $other instanceof self && get_class( $other ) === static::class ) {
            return $this->ordinal === $other->ordinal;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    final public function serialize () {
        return $this->name();
    }

    /**
     * @inheritDoc
     */
    final public function unserialize ( $serialized ) {
        $className = static::class;

        $configuration = self::configuration();
        if ( !array_key_exists( $serialized, $configuration ) ) {
            throw UnknownEnumElement::withName( $className, $serialized );
        }

        [ $ordinal, $arguments ] = $configuration[ $serialized ];

        $this->name = $serialized;
        $this->ordinal = $ordinal;

        if ( !empty( $arguments ) ) {
            try {
                $reflectionClass = new ReflectionClass( $className );
            } catch ( ReflectionException $e ) {
                throw InvalidEnumDefinition::reflectionClassNotFound( $className, $e );
            }

            $constructor = $reflectionClass->getConstructor();
            if ( $constructor && $constructor->getParameters() ) {
                $constructor->setAccessible( true );
                $constructor->invokeArgs( $this, $arguments );
                $constructor->setAccessible( false );
            }
        }
    }
}
