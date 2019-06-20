PHP Addition Repository - Core
==============================

Install
-------

```
composer require par/core
```

Usage
-----

```
interface ObjectInterface
{
    /**
     * Determines if this object equals provided value.
     *
     * @param mixed $other The other value to compare with.
     *
     * @return bool
     */
    public function equals($other): bool;

    /**
     * Returns a string representation of the object. In general, the `toString` method returns a string that "textually represents" this object. The result should be a concise but informative representation that is easy for a person to read.
     *
     * A simple implementation would be:
     * ```
     * return \Par\Core\Helper\InstanceHelper::toString($this);
     * ```
     *
     * @return string
     */
    public function toString(): string;
}
```

```
interface ComparableInterface
{
    /**
     * Compares this object with with other object. Returns a negative integer, zero or a positive integer as this object is less than, equals to, or greater then the other object.
     *
     * @param ComparableInterface $other The other object to be compared.
     *
     * @return int
     * @throws ClassCastException If the other object's type prevents it from being compared to this object.
     */
    public function compareTo(ComparableInterface $other): int;
}
```
