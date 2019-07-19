<?php declare(strict_types=1);

namespace PAR\Enum\PHPUnit;

use PAR\Core\Helper\ClassHelper;
use PAR\Enum\Enum;
use PHPUnit\Framework\TestCase;

class EnumTestCase extends TestCase
{
    public static function clearEnumCache(): void
    {
        // Reset all static properties
        $reflectionClass = ClassHelper::getReflectionClass(Enum::class);

        $constantsProperty = $reflectionClass->getProperty('configuration');
        $constantsProperty->setAccessible(true);
        $constantsProperty->setValue([]);

        $valuesProperty = $reflectionClass->getProperty('instances');
        $valuesProperty->setAccessible(true);
        $valuesProperty->setValue([]);

        $allValuesLoadedProperty = $reflectionClass->getProperty('allInstancesLoaded');
        $allValuesLoadedProperty->setAccessible(true);
        $allValuesLoadedProperty->setValue([]);
    }

    /**
     * This method is called before each test.
     */
    protected function setUp(): void
    {
        self::clearEnumCache();

        parent::setUp();
    }
}
