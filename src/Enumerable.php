<?php

namespace PAR\Enum;

interface Enumerable
{

    /**
     * Returns the enum element of the specified enum type with the specified name. The name must match exactly an
     * identifier used to declare an enum element in this type. (Extraneous whitespace characters are not permitted.)
     *
     * @param string $name The name of the element to return
     *
     * @return static
     */
    public static function valueOf(string $name);

    /**
     * Returns an array containing the elements of this enum type, in the order they are declared.
     *
     * @return static[]
     */
    public static function values(): array;

    /**
     * Returns the name of this enum element, exactly as declared in its declaration.
     *
     * @return string
     */
    public function name(): string;

    /**
     * Returns the ordinal of this enum element (its position in its declaration, where the initial element is assigned an ordinal of zero).
     *
     * @return int
     */
    public function ordinal(): int;

    /**
     * Returns the name of this enum constant, exactly as declared in its declaration.
     *
     * @return string
     */
    public function toString(): string;
}
