<?php


namespace VA\Currency;


/**
 * CurrencyInterface
 *
 * @copyright Via Aurea, s.r.o.
 */
interface CurrencyInterface
{

    /**
     * Currency code.
     * Preferably an ISO code, but it's up to you.
     *
     * @return string
     */
    function code(): string;

}
