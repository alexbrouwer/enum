<?php declare(strict_types=1);

namespace PARTest\Enum;

use PAR\Enum\Enum;
use PAR\Enum\EnumMap;
use PAR\Enum\Exception\InvalidArgumentException;
use PARTest\Enum\Fixtures\Planet;
use PARTest\Enum\Fixtures\WeekDay;
use PHPUnit\Framework\TestCase;
use stdClass;

class EnumMapTest extends TestCase
{
    public function invalidValues(): array
    {
        return [
            ['bool', null, false],
            ['bool', 0],
            ['boolean', 0],
            ['int', 2.4],
            ['integer', 5.3],
            ['float', 3],
            ['double', 7],
            ['string', 1],
            ['object', 1],
            ['array', 1],
            ['callable', 1],
            [stdClass::class, 1],
        ];
    }

    public function testClear(): void
    {
        $map = EnumMap::for(WeekDay::class, 'string', true);
        $map->put(WeekDay::TUESDAY(), 'foo');
        $map->clear();

        self::assertNull($map->get(WeekDay::TUESDAY()));
        self::assertSame(0, $map->size());
    }

    public function testContainsKey(): void
    {
        $map = EnumMap::for(WeekDay::class, 'string', true);
        self::assertFalse($map->containsKey(WeekDay::TUESDAY()));

        $map->put(WeekDay::TUESDAY(), 'foo');
        self::assertTrue($map->containsKey(WeekDay::TUESDAY()));

        $map->put(WeekDay::WEDNESDAY(), null);
        self::assertTrue($map->containsKey(WeekDay::WEDNESDAY()));
    }

    public function testContainsValue(): void
    {
        $map = EnumMap::for(WeekDay::class, 'string', true);
        self::assertFalse($map->containsValue('foo'));

        $map->put(WeekDay::THURSDAY(), 'foo');
        self::assertTrue($map->containsValue('foo'));

        self::assertFalse($map->containsValue(null));

        $map->put(WeekDay::WEDNESDAY(), null);
        self::assertTrue($map->containsValue(null));
    }

    public function testEqualsWithDifferentConstants(): void
    {
        $mapA = EnumMap::for(WeekDay::class, 'string', true);
        $mapA->put(WeekDay::MONDAY(), 'foo');
        $mapB = EnumMap::for(WeekDay::class, 'string', true);
        $mapB->put(WeekDay::TUESDAY(), 'foo');
        self::assertFalse($mapA->equals($mapB));
    }

    public function testEqualsWithDifferentSize(): void
    {
        $mapA = EnumMap::for(WeekDay::class, 'string', true);
        $mapB = EnumMap::for(WeekDay::class, 'string', true);
        $mapB->put(WeekDay::MONDAY(), 'foo');

        self::assertFalse($mapA->equals($mapB));
    }

    public function testEqualsWithDifferentValues(): void
    {
        $mapA = EnumMap::for(WeekDay::class, 'string', true);
        $mapA->put(WeekDay::MONDAY(), 'foo');
        $mapB = EnumMap::for(WeekDay::class, 'string', true);
        $mapB->put(WeekDay::MONDAY(), 'bar');
        self::assertFalse($mapA->equals($mapB));
    }

    public function testEqualsWithSameInstance(): void
    {
        $map = EnumMap::for(WeekDay::class, 'string', true);
        self::assertTrue($map->equals($map));
    }

    public function testForWithInvalidKeyType(): void
    {
        $this->expectException(InvalidArgumentException::class);

        EnumMap::for(stdClass::class, 'string', false);
    }

    public function testForWithInvalidValueType(): void
    {
        $this->expectException(InvalidArgumentException::class);

        EnumMap::for(WeekDay::class, 'foo', false);
    }

    public function testIterator(): void
    {
        $map = EnumMap::for(WeekDay::class, 'string', true);
        $map->put(WeekDay::FRIDAY(), 'foo');
        $map->put(WeekDay::TUESDAY(), 'bar');
        $map->put(WeekDay::SUNDAY(), null);
        $result = [];

        /**
         * @var Enum        $key
         * @var string|null $value
         */
        foreach ($map as $key => $value) {
            $result[$key->ordinal()] = $value;
        }
        self::assertSame([1 => 'bar', 4 => 'foo', 6 => null], $result);
    }

    public function testPutAndGet(): void
    {
        $map = EnumMap::for(WeekDay::class, 'string', true);
        $map->put(WeekDay::TUESDAY(), 'foo');
        $map->put(WeekDay::FRIDAY(), null);

        self::assertSame('foo', $map->get(WeekDay::TUESDAY()));
        self::assertNull($map->get(WeekDay::WEDNESDAY()));
        self::assertNull($map->get(WeekDay::FRIDAY()));
    }

    public function testPutInvalidKey(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $map = EnumMap::for(WeekDay::class, 'string', true);
        $map->put(Planet::MARS(), 'foo');
    }

    /**
     * @dataProvider invalidValues
     *
     * @param string $valueType
     * @param mixed  $value
     * @param bool   $allowNull
     */
    public function testPutInvalidValue(string $valueType, $value, bool $allowNull = true): void
    {
        $this->expectException(InvalidArgumentException::class);

        $map = EnumMap::for(WeekDay::class, $valueType, $allowNull);
        $map->put(WeekDay::TUESDAY(), $value);
    }

    /**
     * @dataProvider validValues
     *
     * @param string $valueType
     * @param mixed  $value
     * @param bool   $allowNull
     */
    public function testPutValidValue(string $valueType, $value, bool $allowNull = true): void
    {
        $map = EnumMap::for(WeekDay::class, $valueType, $allowNull);
        $map->put(WeekDay::TUESDAY(), $value);

        $this->addToAssertionCount(1);
    }

    public function testRemove(): void
    {
        $map = EnumMap::for(WeekDay::class, 'string', true);
        $map->put(WeekDay::TUESDAY(), 'foo');
        $map->remove(WeekDay::TUESDAY());
        $map->remove(WeekDay::WEDNESDAY());

        self::assertNull($map->get(WeekDay::TUESDAY()));
        self::assertSame(0, $map->size());
    }

    public function testSerializeAndUnserialize(): void
    {
        $mapA = EnumMap::for(WeekDay::class, 'string', true);
        $mapA->put(WeekDay::MONDAY(), 'foo');
        $mapB = unserialize(serialize($mapA));
        self::assertTrue($mapA->equals($mapB));
    }

    public function testSize(): void
    {
        $map = EnumMap::for(WeekDay::class, 'string', true);
        self::assertSame(0, $map->size());
        $map->put(WeekDay::MONDAY(), 'foo');
        self::assertSame(1, $map->size());
    }

    public function testToString(): void
    {
        $map = EnumMap::for(WeekDay::class, 'string', true);

        $this->assertSame(sprintf('<%s,?string>', WeekDay::class), $map->toString());
    }

    public function testValues(): void
    {
        $map = EnumMap::for(WeekDay::class, 'string', true);
        self::assertSame([], $map->values());
        $map->put(WeekDay::FRIDAY(), 'foo');
        $map->put(WeekDay::TUESDAY(), 'bar');
        $map->put(WeekDay::SUNDAY(), null);
        self::assertSame(['bar', 'foo', null], $map->values());
    }

    public function validValues(): array
    {
        return [
            ['bool', null],
            ['mixed', 'foo'],
            ['mixed', 1],
            ['mixed', new stdClass()],
            ['bool', true],
            ['boolean', false],
            ['int', 1],
            ['integer', 4],
            ['float', 2.5],
            ['double', 6.4],
            ['string', 'foo'],
            ['object', new stdClass()],
            ['array', ['foo']],
            [
                'callable',
                static function () {
                    return true;
                },
            ],
            [stdClass::class, new stdClass()],
        ];
    }
}
