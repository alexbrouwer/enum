<?php declare(strict_types=1);

namespace PAR\Enum;

use PAR\Core\Helper\ClassHelper;
use PAR\Core\Helper\InstanceHelper;
use PAR\Core\ObjectInterface;
use PAR\Enum\Exception\BadMethodCallException;
use PAR\Enum\Exception\InvalidArgumentException;
use PAR\Enum\Exception\LogicException;
use ReflectionClass;

abstract class Enum implements Enumerable, ObjectInterface
{
    /**
     * @var array<string, array>
     */
    private static $cache = [];

    /**
     * @var int
     */
    private $ordinal;

    /**
     * @var string
     */
    private $name;

    /**
     * Enum should never be created via the new keyword, only via static methods
     *
     * @internal
     * @see static::valueOf()
     */
    final public function __construct()
    {
        // No op.
    }

    /**
     * @inheritDoc
     * @return static[]
     */
    final public static function values(): array
    {
        return array_values(self::resolve());
    }

    private static function resolve(): array
    {

        $class = static::class;
        if (isset(self::$cache[$class])) {
            return self::$cache[$class];
        }

        if (!ClassHelper::isAbstract($class)) {
            throw LogicException::mustBeAbstract($class);
        }

        $reflection = ClassHelper::getReflectionClass($class);
        $elementNames = self::resolveFromDocBlocks($reflection);
        $elementObjects = static::enumerate();

        $elements = self::normalizeElements(
            $class,
            $elementNames,
            $elementObjects
        );

        self::populateElements($elements);

        return self::$cache[$class] = $elements;
    }

    /**
     * Returns list of methods declared via PHP DocBlock.
     *
     * @param ReflectionClass $reflection
     *
     * @return string[]
     */
    private static function resolveFromDocBlocks(ReflectionClass $reflection): array
    {
        $values = [];

        $docComment = $reflection->getDocComment();
        if (!$docComment) {
            return $values;
        }

        preg_match_all(
            '/@method\s+static\s+\S+\s+(.+?)\s*(?:\(\s*\))?\s*\n/',
            $docComment,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            self::assertValidElementName($reflection->getName(), $match[1]);
            $values[] = $match[1];
        }

        return $values;
    }

    private static function assertValidElementName(string $class, string $name): void
    {
        $pattern = '/^[a-zA-Z_][a-zA-Z_0-9]*$/';
        if (preg_match($pattern, $name)) {
            return;
        }

        throw LogicException::invalidName($class, $name, $pattern);
    }

    /**
     * @return array<string, object>
     */
    protected static function enumerate(): array
    {
        return [];
    }

    private static function normalizeElements(string $class, array $elementNames, array $elementObjects): array
    {
        if (count($elementObjects) === 0) {
            return self::createDynamicElementObjects($class, $elementNames);
        }

        $enumeratedElementNames = array_keys($elementObjects);
        $extraEnumeratedKeys = array_diff(
            $enumeratedElementNames,
            $elementNames
        );

        if (count($extraEnumeratedKeys) > 0) {
            throw LogicException::missingElementsInDocBlock(
                $class,
                $extraEnumeratedKeys
            );
        }

        $missingKeysInEnumeration = array_diff(
            $elementNames,
            $enumeratedElementNames
        );
        if (count($missingKeysInEnumeration) > 0) {
            throw LogicException::missingKeysInEnumeration(
                $class,
                $missingKeysInEnumeration
            );
        }

        foreach ($elementObjects as $elementName => $elementObject) {
            self::assertValidElementName($class, (string)$elementName);
            self::assertValidInstance($class, (string)$elementName, $elementObject);
        }

        return $elementObjects;
    }

    private static function createDynamicElementObjects(string $class, array $elementNames): array
    {
        $createdObjects = '';
        foreach ($elementNames as $elementName) {
            if (!method_exists($class, $elementName)) {
                $createdObjects .= sprintf(
                    '"%s" => new class() extends %s {},',
                    $elementName,
                    $class
                );
            }
        }

        return eval(sprintf('return [%s];', $createdObjects));
    }

    private static function assertValidInstance(string $class, string $name, $instance)
    {
        if ($instance instanceof $class) {
            return;
        }

        throw LogicException::invalidInstance($class, $name, $instance);
    }

    private static function populateElements(array $elements): void
    {
        $ordinal = 0;

        foreach ($elements as $name => $element) {
            $element->ordinal = $ordinal;
            $element->name = $name;
            $ordinal++;
        }
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return Enum
     * @throws BadMethodCallException
     */
    final public static function __callStatic($name, $arguments): self
    {
        if (static::isValidName($name)) {
            return static::valueOf($name);
        }

        throw BadMethodCallException::undefinedMethod(static::class, $name);
    }

    private static function isValidName(string $name): bool
    {
        return array_key_exists($name, self::resolve());
    }

    /**
     * @inheritDoc
     */
    final public static function valueOf(string $name): self
    {
        if (static::isValidName($name)) {
            return static::resolve()[$name];
        }

        throw InvalidArgumentException::unknownElement(static::class, $name);
    }

    /**
     * @inheritDoc
     */
    final public function ordinal(): int
    {
        if (null === $this->ordinal) {
            throw LogicException::notAllowedInEnumerate($this, 'ordinal');
        }

        return $this->ordinal;
    }

    /**
     * @inheritDoc
     */
    final public function toString(): string
    {
        return $this->name();
    }

    /**
     * @inheritDoc
     */
    final public function name(): string
    {
        if (null === $this->name) {
            throw LogicException::notAllowedInEnumerate($this, 'name');
        }

        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function equals($other): bool
    {
        /* @var static $other */
        return InstanceHelper::isOfClass($other, static::class)
            && $this->name() === $other->name();
    }
}
