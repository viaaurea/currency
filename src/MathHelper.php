<?php


namespace VA\Currency;


use VA\Currency\Exceptions\MathException;

/**
 * Math wrapper.
 *
 * GMP or BC Math usage can be implemented here, to be used throughout the package.
 *
 * @copyright Via Aurea, s.r.o.
 */
class MathHelper
{

    /**
     * toNumber( $a )
     * @return float|int
     */
    static function number($a)
    {
        if (is_numeric($a)) {
            return $a + 0;
        }
        throw new MathException('Given value is not a number.');
    }


    /**
     * $a + $b
     * @return float|int
     */
    static function add($a, $b)
    {
        return $a + $b;
    }


    /**
     * $a - $b
     * @return float|int
     */
    static function sub($a, $b)
    {
        return $a - $b;
    }


    /**
     * $a * $b
     * @return float|int
     */
    static function mul($a, $b)
    {
        return $a * $b;
    }


    /**
     * $a / $b
     * @return float|int
     */
    static function div($a, $b)
    {
        if ($b == 0) { // intentionally ==
            throw new MathException('Division by zero.');
        }
        return $a / $b;
    }


    /**
     * $a == $b
     * @return bool
     */
    static function eq($a, $b): bool
    {
        return $a == $b; // intentionally ==
    }


    /**
     * $a > $b
     * @return bool
     */
    static function gt($a, $b): bool
    {
        return $a > $b;
    }


    /**
     * $a < $b
     * @return bool
     */
    static function lt($a, $b): bool
    {
        return $a < $b;
    }


    /**
     * $a >= $b
     * @return bool
     */
    static function gte($a, $b): bool
    {
        return static::eq($a, $b) || static::gt($a, $b);
    }


    /**
     * $a <= $b
     * @return bool
     */
    static function lte($a, $b): bool
    {
        return static::eq($a, $b) || static::lt($a, $b);
    }

}