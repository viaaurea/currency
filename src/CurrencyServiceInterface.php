<?php


namespace VA\Currency;


/**
 * Interface for services that provide currency comparison methods and money object factory method.
 *
 * @copyright Via Aurea, s.r.o.
 */
interface CurrencyServiceInterface
{


    /**
     * @return MoneyInterface
     */
    function create($amount, $currency): MoneyInterface;


    /**
     * $second - $first
     *
     * @param MoneyInterface $first
     * @param MoneyInterface $second
     * @return MoneyInterface
     */
    function diff(MoneyInterface $first, MoneyInterface $second): MoneyInterface;


    /**
     * $first == $second
     *
     * @param MoneyInterface $first
     * @param MoneyInterface $second
     * @return bool
     */
    function equal(MoneyInterface $first, MoneyInterface $second): bool;


    /**
     * $first != $second
     *
     * @param MoneyInterface $first
     * @param MoneyInterface $second
     * @return bool
     */
    function notEqual(MoneyInterface $first, MoneyInterface $second): bool;


    /**
     * $first < $second
     *
     * @param MoneyInterface $first
     * @param MoneyInterface $second
     * @return bool
     */
    function lessThan(MoneyInterface $first, MoneyInterface $second): bool;


    /**
     * $first <= $second
     *
     * @param MoneyInterface $first
     * @param MoneyInterface $second
     * @return bool
     */
    function lessThanOrEqualTo(MoneyInterface $first, MoneyInterface $second): bool;


    /**
     * $first > $second
     *
     * @param MoneyInterface $first
     * @param MoneyInterface $second
     * @return bool
     */
    function greaterThan(MoneyInterface $first, MoneyInterface $second): bool;


    /**
     * $first >= $second
     *
     * @param MoneyInterface $first
     * @param MoneyInterface $second
     * @return bool
     */
    function greaterThanOrEqualTo(MoneyInterface $first, MoneyInterface $second): bool;

}
