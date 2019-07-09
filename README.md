PHP Addition Repository - Enum
==============================

[![Build Status](https://travis-ci.org/php-addition-repository/enum.svg?branch=master)](https://travis-ci.org/php-addition-repository/enum)
[![Coverage Status](https://coveralls.io/repos/github/php-addition-repository/enum/badge.svg?branch=master)](https://coveralls.io/github/php-addition-repository/enum?branch=master)

This library is strongly based on <https://github.com/DASPRiD/Enum>, but with some minor changes.

1. It uses the `@method` lines in the class doc block to determine the values (not constants).
3. Your enum class must be declared `final` or `abstract`. 

Installation
------------

```bash
composer require par/enum
```

Usage
-----

### Basics

All enum implementations must extends `PAR\Enum\Enum` and declare the values in its class doc block.

```php

use PAR\Enum\Enum;

/**
 * @method static self MONDAY()
 * @method static self TUESDAY()
 * @method static self WEDNESDAY()
 * @method static self THURSDAY()
 * @method static self FRIDAY()
 * @method static self SATURDAY()
 * @method static self SUNDAY()
 */
final class WeekDay extends Enum
{
}
```

This will allow you to use it like:

```php
function tellItLikeItIs(WeekDay $weekDay)
{
    switch ($weekDay) {
        case WeekDay::MONDAY():
            echo 'Mondays are bad.';
            break;
            
        case WeekDay::FRIDAY():
            echo 'Fridays are better.';
            break;
            
        case WeekDay::SATURDAY():
        case WeekDay::SUNDAY():
            echo 'Weekends are best.';
            break;
            
        default:
            echo 'Midweek days are so-so.';
    }
}

tellItLikeItIs(WeekDay::MONDAY());
tellItLikeItIs(WeekDay::WEDNESDAY());
tellItLikeItIs(WeekDay::FRIDAY());
tellItLikeItIs(WeekDay::SATURDAY());
tellItLikeItIs(WeekDay::SUNDAY());
```

### Complex example

```php

use PAR\Enum\Enum;

/**
 * @method static self MERCURY()
 * @method static self VENUS()
 * @method static self EARTH()
 * @method static self MARS()
 * @method static self JUPITER()
 * @method static self SATURN()
 * @method static self URANUS()
 * @method static self NEPTUNE()
 */
final class Planet extends Enum
{
    protected const MERCURY = [3.303e+23, 2.4397e6];
    protected const VENUS = [4.869e+24, 6.0518e6];
    protected const EARTH = [5.976e+24, 6.37814e6];
    protected const MARS = [6.421e+23, 3.3972e6];
    protected const JUPITER = [1.9e+27, 7.1492e7];
    protected const SATURN = [5.688e+26, 6.0268e7];
    protected const URANUS = [8.686e+25, 2.5559e7];
    protected const NEPTUNE = [1.024e+26, 2.4746e7];

    /**
     * Universal gravitational constant.
     *
     * @var float
     */
    private const G = 6.67300E-11;

    /**
     * @var float
     */
    private $mass;

    /**
     * @var float
     */
    private $radius;

    protected function __construct(float $mass, float $radius)
    {
        $this->mass = $mass;
        $this->radius = $radius;
    }

    public function mass(): float
    {
        return $this->mass;
    }

    public function radius(): float
    {
        return $this->radius;
    }

    public function surfaceGravity() : float
    {
        return self::G * $this->mass / ($this->radius * $this->radius);
    }

    public function surfaceWeight(float $otherMass) : float
    {
        return $otherMass * $this->surfaceGravity();
    }
}

$myMass = 80;

foreach(Planet::values() as $planet) {
    echo sprintf('Your weight on %s is %f', $planet, $planet->surfaceWeight($myMass)) . PHP_EOL;
    
}
```
