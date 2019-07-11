<?php declare(strict_types=1);

namespace PARTest\Enum;

use PAR\Core\Exception\ClassMismatchException;
use PAR\Core\PHPUnit\CoreAssertions;
use PAR\Enum\Exception\CloneNotSupportedException;
use PAR\Enum\Exception\InvalidClassException;
use PAR\Enum\Exception\MissingConstantsException;
use PAR\Enum\Exception\SerializeNotSupportedException;
use PAR\Enum\Exception\UnknownEnumException;
use PAR\Enum\Exception\UnserializeNotSupportedException;
use PAR\Enum\PHPUnit\EnumTestCase;
use PARTest\Enum\Fixtures\MissingConstantEnum;
use PARTest\Enum\Fixtures\NonFinalOrAbstractEnum;
use PARTest\Enum\Fixtures\Planet;
use PARTest\Enum\Fixtures\WeekDay;

class EnumTest extends EnumTestCase
{
    use CoreAssertions;

    public function testToString(): void
    {
        $weekday = WeekDay::FRIDAY();

        $this->assertSame('FRIDAY', $weekday->toString());
        self::assertSame('FRIDAY', (string)$weekday);
    }

    public function testName(): void
    {
        self::assertSame('THURSDAY', WeekDay::THURSDAY()->name());
    }

    public function testOrdinal(): void
    {
        self::assertSame(2, WeekDay::WEDNESDAY()->ordinal());
    }

    public function testStrictComparison(): void
    {
        self::assertSameObject(WeekDay::FRIDAY(), WeekDay::FRIDAY());
        self::assertNotSameObject(WeekDay::FRIDAY(), WeekDay::SATURDAY());
    }

    public function testValueOf(): void
    {
        self::assertSameObject(WeekDay::SUNDAY(), WeekDay::valueOf('SUNDAY'));
    }

    public function testValueOfWithInvalidName(): void
    {
        $this->expectException(UnknownEnumException::class);

        WeekDay::valueOf('MAANDAG');
    }

    public function testCloneNotSupported(): void
    {
        $this->expectException(CloneNotSupportedException::class);

        /** @noinspection PhpExpressionResultUnusedInspection */
        clone WeekDay::FRIDAY();
    }

    public function testSerializeNotSupported(): void
    {
        $this->expectException(SerializeNotSupportedException::class);

        serialize(WeekDay::FRIDAY());
    }

    public function testUnserializeNotSupported(): void
    {
        $this->expectException(UnserializeNotSupportedException::class);

        unserialize(sprintf('O:%d:"%s":0:{}', strlen(WeekDay::class), WeekDay::class));
    }

    public function testValues(): void
    {
        $this->assertSame(
            [
                WeekDay::MONDAY(),
                WeekDay::TUESDAY(),
                WeekDay::WEDNESDAY(),
                WeekDay::THURSDAY(),
                WeekDay::FRIDAY(),
                WeekDay::SATURDAY(),
                WeekDay::SUNDAY(),
            ], WeekDay::values()
        );
    }

    public function testReturnValueOfValuesIsSortedByOrdinal(): void
    {
        // Initialize some out of order
        WeekDay::SATURDAY();
        WeekDay::TUESDAY();

        $ordinals = array_values(
            array_map(
                static function (WeekDay $weekDay): int {
                    return $weekDay->ordinal();
                },
                WeekDay::values()
            )
        );

        self::assertSame([0, 1, 2, 3, 4, 5, 6], $ordinals);
    }

    public function testCompareTo(): void
    {
        self::assertSame(-4, WeekDay::WEDNESDAY()->compareTo(WeekDay::SUNDAY()));
        self::assertSame(4, WeekDay::SUNDAY()->compareTo(WeekDay::WEDNESDAY()));
        self::assertSame(0, WeekDay::WEDNESDAY()->compareTo(WeekDay::WEDNESDAY()));
    }

    public function testCompareToWrongType(): void
    {
        $this->expectException(ClassMismatchException::class);

        WeekDay::MONDAY()->compareTo(Planet::EARTH());
    }

    public function testParameterizedEnum(): void
    {
        $planet = Planet::EARTH();

        self::assertSame(5.976e+24, $planet->mass());
        self::assertSame(6.37814e6, $planet->radius());
    }

    public function testNonAbstractOrFinalEnumThrowsException(): void
    {
        $this->expectException(InvalidClassException::class);

        NonFinalOrAbstractEnum::values();
    }

    public function testAllMethodsMustHaveAConstant(): void
    {
        $this->expectException(MissingConstantsException::class);

        MissingConstantEnum::values();
    }
}
