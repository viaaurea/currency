<?php

declare(strict_types=1);

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
    public function create($amount, $currency): MoneyInterface;


    /**
     * $second - $first
     *
     * @param MoneyInterface $first
     * @param MoneyInterface $second
     * @return MoneyInterface
     */
    public function diff(MoneyInterface $first, MoneyInterface $second): MoneyInterface;


    /**
     * $first == $second
     *
     * @param MoneyInterface $first
     * @param MoneyInterface $second
     * @return bool
     */
    public function equal(MoneyInterface $first, MoneyInterface $second): bool;


    /**
     * $first != $second
     *
     * @param MoneyInterface $first
     * @param MoneyInterface $second
     * @return bool
     */
    public function notEqual(MoneyInterface $first, MoneyInterface $second): bool;


    /**
     * $first < $second
     *
     * @param MoneyInterface $first
     * @param MoneyInterface $second
     * @return bool
     */
    public function lessThan(MoneyInterface $first, MoneyInterface $second): bool;


    /**
     * $first <= $second
     *
     * @param MoneyInterface $first
     * @param MoneyInterface $second
     * @return bool
     */
    public function lessThanOrEqualTo(MoneyInterface $first, MoneyInterface $second): bool;


    /**
     * $first > $second
     *
     * @param MoneyInterface $first
     * @param MoneyInterface $second
     * @return bool
     */
    public function greaterThan(MoneyInterface $first, MoneyInterface $second): bool;


    /**
     * $first >= $second
     *
     * @param MoneyInterface $first
     * @param MoneyInterface $second
     * @return bool
     */
    public function greaterThanOrEqualTo(MoneyInterface $first, MoneyInterface $second): bool;
}
