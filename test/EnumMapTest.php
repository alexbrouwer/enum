<?php

declare(strict_types=1);

namespace PARTest\Enum;

use PAR\Enum\EnumMap;
use PAR\Enum\Exception\InvalidEnumMapDefinition;
use PAR\Enum\Exception\InvalidEnumMapKey;
use PAR\Enum\Exception\InvalidEnumMapValue;
use PARTest\Enum\Fixtures\Planet;
use PARTest\Enum\Fixtures\WeekDay;
use PHPUnit\Framework\TestCase;
use stdClass;

class EnumMapTest extends TestCase
{

    /**
     * @test
     */
    public function itCanBeIterated(): void
    {
        $map = EnumMap::for(Planet::class, 'string', false);
        $map->put(Planet::MARS(), 'mars');
        $map->put(Planet::EARTH(), 'aarde');
        $map->put(Planet::NEPTUNE(), 'neptunes');

        $this->assertTrue(is_iterable($map));

        $elements = [];
        $values = [];
        foreach ($map as $element => $value) {
            $elements[] = $element;
            $values[] = $value;
        }

        $this->assertSame(
            [
                Planet::EARTH(),
                Planet::MARS(),
                Planet::NEPTUNE(),
            ],
            $elements
        );
        $this->assertSame(
            [
                'aarde',
                'mars',
                'neptunes',
            ],
            $values
        );
    }

    /**
     * @test
     */
    public function itCanBeSerialized(): void
    {
        $map = $this->createPlanetTranslationsMap(true);
        $map->put(Planet::MARS(), null);

        $serialized = serialize($map);

        $mapFromSerialization = unserialize($serialized);

        $this->assertTrue($map->equals($mapFromSerialization));
    }

    /**
     * @test
     */
    public function itCanBeTransformedToString(): void
    {
        $this->assertSame(
            sprintf('<%s,int>', Planet::class),
            EnumMap::for(Planet::class, 'int', false)->toString()
        );

        $this->assertSame(
            sprintf('<%s,?string>', Planet::class),
            EnumMap::for(Planet::class, 'string', true)->toString()
        );
    }

    /**
     * @test
     */
    public function itCanClearAllValues(): void
    {
        $map = $this->createPlanetTranslationsMap(false);
        $this->assertSame(8, $map->size());

        $map->clear();

        $this->assertSame(0, $map->size());
        $this->assertSame([], $map->values());
    }

    /**
     * @test
     */
    public function itCanCreateAStateMap(): void
    {
        $map = EnumMap::withState(WeekDay::class, false);

        $this->assertSame(count(WeekDay::values()), $map->size());
        $this->assertTrue($map->containsValue(false));
        $this->assertFalse($map->containsValue(true));
    }

    /**
     * @test
     */
    public function itCanDetermineEqualityWithDifferentContent(): void
    {
        $map = EnumMap::for(Planet::class, 'string', true);
        $map->put(Planet::EARTH(), 'aarde');

        $otherMap = EnumMap::for(Planet::class, 'string', true);
        $otherMap->put(Planet::EARTH(), 'earth');

        $this->assertFalse($map->equals($otherMap));
    }

    /**
     * @test
     */
    public function itCanDetermineEqualityWithDifferentKeyType(): void
    {
        $map = EnumMap::for(Planet::class, 'string', true);
        $otherMap = EnumMap::for(WeekDay::class, 'string', true);

        $this->assertFalse($map->equals($otherMap));
    }

    /**
     * @test
     */
    public function itCanDetermineEqualityWithDifferentSupportForNull(): void
    {
        $map = EnumMap::for(Planet::class, 'string', true);
        $otherMap = EnumMap::for(Planet::class, 'string', false);

        $this->assertFalse($map->equals($otherMap));
    }

    /**
     * @test
     */
    public function itCanDetermineEqualityWithDifferentValue(): void
    {
        $map = EnumMap::for(Planet::class, 'string', true);

        $this->assertFalse($map->equals(new stdClass()));
        $this->assertFalse($map->equals(null));
    }

    /**
     * @test
     */
    public function itCanDetermineEqualityWithSameDefinitionAndContent(): void
    {
        $map = EnumMap::for(Planet::class, 'string', true);
        $map->put(Planet::EARTH(), 'aarde');

        $otherMap = EnumMap::for(Planet::class, 'string', true);
        $otherMap->put(Planet::EARTH(), 'aarde');

        $this->assertTrue($map->equals($otherMap));
    }

    /**
     * @test
     */
    public function itCanDetermineEqualityWithSameInstance(): void
    {
        $map = EnumMap::for(Planet::class, 'string', true);

        $this->assertTrue($map->equals($map));
    }

    /**
     * @test
     */
    public function itCanPutANullValueWhenAllowed(): void
    {
        $map = EnumMap::for(Planet::class, 'string', true);

        $this->assertNull($map->get(Planet::EARTH()));
        $this->assertSame(0, $map->size());

        $map->put(Planet::EARTH(), 'aarde');
        $this->assertNotNull($map->get(Planet::EARTH()));
        $this->assertSame(1, $map->size());

        $map->put(Planet::EARTH(), null);
        $this->assertNull($map->get(Planet::EARTH()));
        $this->assertSame(1, $map->size());
    }

    /**
     * @test
     */
    public function itCanPutAValueForAnElement(): void
    {
        $map = EnumMap::for(Planet::class, 'string', false);

        $this->assertNull($map->get(Planet::EARTH()));
        $this->assertSame(0, $map->size());

        $map->put(Planet::EARTH(), 'aarde');

        $this->assertSame('aarde', $map->get(Planet::EARTH()));
        $this->assertSame(1, $map->size());
    }

    /**
     * @test
     */
    public function itCanRemoveElementByKey(): void
    {
        $map = EnumMap::for(Planet::class, 'string', false);
        $map->put(Planet::MARS(), 'mars');

        $this->assertSame('mars', $map->remove(Planet::MARS()));
        $this->assertNull($map->get(Planet::MARS()));
        $this->assertSame(0, $map->size());
    }

    /**
     * @test
     */
    public function itCanReturnOnlyValues(): void
    {
        $map = EnumMap::for(Planet::class, 'string', false);
        $map->put(Planet::MARS(), 'mars');
        $map->put(Planet::EARTH(), 'aarde');
        $map->put(Planet::NEPTUNE(), 'neptunes');

        $this->assertSame(
            [
                'aarde',
                'mars',
                'neptunes',
            ],
            $map->values()
        );
    }

    /**
     * @test
     */
    public function itCanTestIfKeyIsNotPresent(): void
    {
        $map = $this->createPlanetTranslationsMap(false);
        $map->remove(Planet::MARS());

        $this->assertFalse($map->containsKey(Planet::MARS()));
    }

    /**
     * @test
     */
    public function itCanTestIfKeyIsPresent(): void
    {
        $map = $this->createPlanetTranslationsMap(false);

        $this->assertTrue($map->containsKey(Planet::EARTH()));
    }

    /**
     * @test
     */
    public function itCanTestIfKeyWithNullValueIsPresentWhenNullIsAllowed(): void
    {
        $map = $this->createPlanetTranslationsMap(true);
        $map->put(Planet::EARTH(), null);

        $this->assertTrue($map->containsKey(Planet::EARTH()));
    }

    /**
     * @test
     */
    public function itCanTestIfNullIsPresentWhenMapIsNullable(): void
    {
        $map = $this->createPlanetTranslationsMap(true);
        $map->put(Planet::EARTH(), null);

        $this->assertTrue($map->containsValue(null));
    }

    /**
     * @test
     */
    public function itCanTestIfValueIsNotPresent(): void
    {
        $map = $this->createPlanetTranslationsMap(true);

        $this->assertFalse($map->containsValue('foobar'));
    }

    /**
     * @test
     */
    public function itCanTestIfValueIsPresent(): void
    {
        $map = $this->createPlanetTranslationsMap(true);
        $map->put(Planet::EARTH(), 'earth');

        $this->assertTrue($map->containsValue('earth'));
    }

    /**
     * @test
     */
    public function itCannotPutANullValueWhenNotAllowed(): void
    {
        $map = EnumMap::for(Planet::class, 'string', false);

        $this->expectExceptionObject(
            InvalidEnumMapValue::nullNotAllowedFor(Planet::MARS())
        );

        $map->put(Planet::MARS(), null);
    }

    /**
     * @test
     */
    public function itCannotPutAValueForADifferentKey(): void
    {
        $map = EnumMap::for(Planet::class, 'string', false);

        $this->expectExceptionObject(
            InvalidEnumMapKey::withKey(WeekDay::MONDAY(), Planet::class)
        );

        $map->put(WeekDay::MONDAY(), 'maandag');
    }

    /**
     * @test
     */
    public function itCannotPutAValueWithADifferentType(): void
    {
        $map = EnumMap::for(Planet::class, 'string', false);

        $this->expectExceptionObject(
            InvalidEnumMapValue::wrongTypeFor(Planet::MARS(), 1, 'string')
        );

        $map->put(Planet::MARS(), 1);
    }

    /**
     * @test
     */
    public function itCreatesAnEmptyInstance(): void
    {
        $map = EnumMap::for(Planet::class, 'string', false);

        $this->assertSame(0, $map->size());
    }

    /**
     * @test
     */
    public function itThrowsAnExceptionWhenCreateMapForUnknownValueType(): void
    {
        $this->expectException(InvalidEnumMapDefinition::class);

        EnumMap::for(Planet::class, 'foo', false);
    }

    /**
     * @test
     */
    public function itThrowsAnExceptionWhenCreatingMapForNonEnumerableKey(): void
    {
        $this->expectException(InvalidEnumMapDefinition::class);

        EnumMap::for('string', 'string', false);
    }

    /**
     * @test
     */
    public function itWillReturnPreviousValueOfElementWhenChangingTheElement(): void
    {
        $map = EnumMap::for(Planet::class, 'string', false);

        $this->assertNull($map->put(Planet::EARTH(), 'aarde'));
        $this->assertSame('aarde', $map->put(Planet::EARTH(), 'earth'));
    }

    private function createPlanetTranslationsMap(bool $allowNullValues): EnumMap
    {
        $map = EnumMap::for(Planet::class, 'string', $allowNullValues);
        $map->put(Planet::MERCURY(), 'Mercurius');
        $map->put(Planet::VENUS(), 'Venus');
        $map->put(Planet::EARTH(), 'Aarde');
        $map->put(Planet::MARS(), 'Mars');
        $map->put(Planet::JUPITER(), 'Jupiter');
        $map->put(Planet::SATURN(), 'Saturnus');
        $map->put(Planet::URANUS(), 'Uranus');
        $map->put(Planet::NEPTUNE(), 'Neptunes');

        return $map;
    }
}
