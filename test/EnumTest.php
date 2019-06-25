<?php

namespace PARTest\Enum;

use PAR\Enum\Exception\BadMethodCallException;
use PAR\Enum\Exception\InvalidArgumentException;
use PAR\Enum\Exception\LogicException;
use PARTest\Enum\Fixtures\InvalidElementNameEnum;
use PARTest\Enum\Fixtures\MissingElementInDocBlockEnum;
use PARTest\Enum\Fixtures\MissingElementInEnumerateEnum;
use PARTest\Enum\Fixtures\NameWithinEnumerateEnum;
use PARTest\Enum\Fixtures\NonAbstractEnum;
use PARTest\Enum\Fixtures\OrdinalWithinEnumerateEnum;
use PARTest\Enum\Fixtures\ValidEnum;
use PARTest\Enum\Fixtures\WrongElementInstanceInEnumerateEnum;
use PHPUnit\Framework\TestCase;

class EnumTest extends TestCase
{

    public function testThatElementsFromDocBlockAreAvailable(): void
    {
        $elements = ValidEnum::values();

        $this->assertCount(2, $elements);
        $this->assertContains(ValidEnum::ELEMENT_A(), $elements);
        $this->assertContains(ValidEnum::ELEMENT_B(), $elements);
    }

    public function testThatElementsHaveName(): void
    {
        $this->assertSame('ELEMENT_A', ValidEnum::ELEMENT_A()->name());
        $this->assertSame('ELEMENT_B', ValidEnum::ELEMENT_B()->name());
    }

    public function testThatElementsHaveOrdinal(): void
    {
        $this->assertSame(0, ValidEnum::ELEMENT_A()->ordinal());
        $this->assertSame(1, ValidEnum::ELEMENT_B()->ordinal());
    }

    public function testThatElementsHaveString(): void
    {
        $this->assertSame('ELEMENT_A', ValidEnum::ELEMENT_A()->toString());
    }

    public function testThatEqualityCanBeDeterminedBetweenElements(): void
    {
        $this->assertTrue(ValidEnum::ELEMENT_A()->equals(ValidEnum::ELEMENT_A()));
        $this->assertFalse(ValidEnum::ELEMENT_A()->equals(ValidEnum::ELEMENT_B()));
    }

    public function testThatValuesCanBeReturned(): void
    {
        $this->assertSame(
            [
                ValidEnum::ELEMENT_A(),
                ValidEnum::ELEMENT_B(),
            ],
            ValidEnum::values()
        );
    }

    public function testThatValueOfReturnsExpectedElement(): void
    {
        $actual = ValidEnum::valueOf('ELEMENT_A');

        $this->assertSame('ELEMENT_A', $actual->name());
    }

    public function testThatValueOfThrowsExceptionWhenElementDoesNotExist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Enum PARTest\Enum\Fixtures\ValidEnum does not have an element "I_DO_NOT_EXIST".'
        );

        ValidEnum::valueOf('I_DO_NOT_EXIST');
    }

    public function testThatNonAbstractEnumThrowsException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Enum PARTest\Enum\Fixtures\NonAbstractEnum must be declared abstract.');

        NonAbstractEnum::values();
    }

    public function testThatInvalidElementNameThrowsException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            'Element name "INVA LID" in ' . InvalidElementNameEnum::class . ' does not match pattern /^[a-zA-Z_][a-zA-Z_0-9]*$/.'
        );

        InvalidElementNameEnum::values();
    }

    public function testThatElementsInEnumerateMustBeDeclaredInDocBlock(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            'Enum PARTest\Enum\Fixtures\MissingElementInEnumerateEnum is missing key(s) [ELEMENT_B] in enumerate().'
        );

        MissingElementInEnumerateEnum::values();
    }

    public function testThatElementsInDocBlockMustBeDeclaredInEnumerate(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            'Enum ' . MissingElementInDocBlockEnum::class . ' is missing "@method static self ...()" declaration(s) ' .
            'for [UNDEFINED_ELEMENT] in class docComment.'
        );

        MissingElementInDocBlockEnum::values();
    }

    public function testThatOrdinalThrowsExceptionWithinEnumerate(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            'Cannot call ordinal() within ' . OrdinalWithinEnumerateEnum::class . '::enumerate().'
        );

        OrdinalWithinEnumerateEnum::ELEMENT_A();
    }

    public function testThatNameThrowsExceptionWithinEnumerate(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            'Cannot call name() within ' . NameWithinEnumerateEnum::class . '::enumerate().'
        );

        NameWithinEnumerateEnum::ELEMENT_A();
    }

    public function testThatAccessingNonExistingElementThrowsException(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Call to undefined method PARTest\Enum\Fixtures\ValidEnum::NON_EXISTING');

        forward_static_call(ValidEnum::class . '::NON_EXISTING');
    }

    public function testThatReturningWrongElementInstanceInEnumerateThrowsException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            'Key "ELEMENT_B" in in ' . WrongElementInstanceInEnumerateEnum::class
            . '::enumerate() must contain an instance of ' . WrongElementInstanceInEnumerateEnum::class
            . ', got an instance of PARTest\Enum\Fixtures\ValidEnum.'
        );

        WrongElementInstanceInEnumerateEnum::values();
    }

    // test clone, set_state, sleep, wakeup, serialize, unserialize
}
