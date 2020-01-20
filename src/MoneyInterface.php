<?php

namespace VA\Currency;

/**
 * MoneyInterface
 *
 * @copyright Via Aurea, s.r.o.
 */
interface MoneyInterface
{
    /**
     * Get the amount of the value object.
     *
     * @return int|double
     */
    public function amount();


    /**
     * Get the currency of the value object.
     *
     * @return CurrencyInterface
     */
    public function currency(): CurrencyInterface;
}
