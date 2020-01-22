<?php declare( strict_types=1 );

namespace PARTest\Enum;

use PAR\Enum\Enum;
use PAR\Enum\Exception\InvalidEnumDefinition;
use PAR\Enum\Exception\UnknownEnumElement;
use PARTest\Enum\Fixtures\MissingElementConstantEnum;
use PARTest\Enum\Fixtures\NotFinalOrAbstractEnum;
use PARTest\Enum\Fixtures\Planet;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

class EnumTest extends TestCase {

    /**
     * @throws ReflectionException
     */
    public static function clearEnumCache (): void {
        // Reset all static properties
        $reflectionClass = new ReflectionClass( Enum::class );

        $constantsProperty = $reflectionClass->getProperty( 'configuration' );
        $constantsProperty->setAccessible( true );
        $constantsProperty->setValue( [] );

        $valuesProperty = $reflectionClass->getProperty( 'instances' );
        $valuesProperty->setAccessible( true );
        $valuesProperty->setValue( [] );

        $allValuesLoadedProperty = $reflectionClass->getProperty( 'allInstancesLoaded' );
        $allValuesLoadedProperty->setAccessible( true );
        $allValuesLoadedProperty->setValue( [] );
    }

    /**
     * @test
     */
    public function itCanBeCloned (): void {
        $planet = Planet::EARTH();

        $clonedPlanet = clone $planet;

        $this->assertTrue( $planet->equals( $clonedPlanet ) );
        $this->assertSame( 'EARTH', $clonedPlanet->name() );
    }

    /**
     * @test
     */
    public function itCanBeTransformedToString (): void {
        $this->assertSame( 'MERCURY', Planet::MERCURY()->toString() );
    }

    /**
     * @test
     */
    public function itCanDetermineEqualityWithOtherValues (): void {
        $element = Planet::EARTH();
        $otherElement = Planet::NEPTUNE();

        $this->assertTrue( $element->equals( Planet::EARTH() ) );

        $this->assertFalse( $element->equals( $otherElement ) );
        $this->assertFalse( $element->equals( null ) );
        $this->assertFalse( $element->equals( 'EARTH' ) );
        $this->assertFalse( $element->equals( $this ) );
    }

    /**
     * @test
     */
    public function itCanFindAnElementByName (): void {
        $element = Planet::valueOf( 'EARTH' );

        $this->assertInstanceOf( Planet::class, $element );
        $this->assertSame( 'EARTH', $element->name() );
    }

    /**
     * @test
     */
    public function itCanReturnAnArrayOfElements (): void {
        $this->assertSame(
            [
                Planet::MERCURY(),
                Planet::VENUS(),
                Planet::EARTH(),
                Planet::MARS(),
                Planet::JUPITER(),
                Planet::SATURN(),
                Planet::URANUS(),
                Planet::NEPTUNE(),
            ],
            Planet::values()
        );
    }

    /**
     * @test
     */
    public function itCreatesElementWithNameAsDefinedInMethodAnnotation (): void {
        $this->assertSame( 'MARS', Planet::MARS()->name() );
    }

    /**
     * @test
     */
    public function itCreatesElementWithOrdinalEqualToPositionInMethodAnnotationDefinitions (): void {
        $this->assertSame( 3, Planet::MARS()->ordinal() );
    }

    /**
     * @test
     */
    public function itCreatesElementsPassingValuesOfProtectedConstantsToConstructor (): void {
        $this->assertSame( 1.9E+27, Planet::JUPITER()->mass() );
    }

    /**
     * @test
     */
    public function itSupportsDeserialization (): void {
        $planet = Planet::MARS();
        $serialized = 'C:28:"PARTest\Enum\Fixtures\Planet":4:{MARS}';

        /** @var Planet $deserialized */
        $deserialized = unserialize( $serialized );

        $this->assertInstanceOf( Planet::class, $deserialized );
        $this->assertSame( $planet->name(), $deserialized->name() );
    }

    /**
     * @test
     */
    public function itSupportsSerialization (): void {
        $serialized = serialize( Planet::EARTH() );

        $this->assertSame( 'C:28:"PARTest\Enum\Fixtures\Planet":5:{EARTH}', $serialized );
    }

    /**
     * @test
     */
    public function itWillThrowAnExceptionWhenClassIsNotFinalOrAbstract (): void {
        $this->expectExceptionObject(
            InvalidEnumDefinition::classMustBeFinalOrAbstract(
                NotFinalOrAbstractEnum::class
            )
        );

        NotFinalOrAbstractEnum::values();
    }

    /**
     * @test
     */
    public function itWillThrowAnExceptionWhenElementConstantsAreMissingOrInvalid (): void {
        $this->expectExceptionObject(
            InvalidEnumDefinition::missingClassConstants(
                MissingElementConstantEnum::class,
                [ 'PUBLIC_CONST', 'PRIVATE_CONST', 'MISSING_CONST' ]
            )
        );

        MissingElementConstantEnum::values();
    }

    /**
     * @test
     */
    public function itWillThrowAnExceptionWhenElementDoesNotExist (): void {
        $this->expectExceptionObject(
            UnknownEnumElement::withName( Planet::class, 'SUN' )
        );

        Planet::valueOf( 'SUN' );
    }

    /**
     * This method is called before each test.
     */
    protected function setUp (): void {
        self::clearEnumCache();

        parent::setUp();
    }
}
