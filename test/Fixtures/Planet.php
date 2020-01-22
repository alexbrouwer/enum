<?php

declare(strict_types=1);

namespace PARTest\Enum\Fixtures;

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
     * @var float
     */
    private $mass;

    /**
     * @var float
     */
    private $radius;

    public function mass(): float
    {
        return $this->mass;
    }

    public function radius(): float
    {
        return $this->radius;
    }

    protected function __construct(float $mass, float $radius)
    {
        parent::__construct();
        $this->mass = $mass;
        $this->radius = $radius;
    }
}
