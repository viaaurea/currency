<?php


namespace VA\Currency;


use ReflectionClass;

/**
 * CurrencyList.
 *
 * ISO 4217 - https://en.wikipedia.org/wiki/ISO_4217
 *
 * @deprecated this class/interface was used for internal purposes, not suitable for public domain
 */
class CurrencyList implements CurrencyIsoCodes
{


    /**
     * Všechny měny ( skoro všechny )
     * @return array
     */
    public static function allCurrencies()
    {
        $class = new ReflectionClass(__CLASS__);
        return (array)$class->getConstants();
    }


    public static function isValidCurrency(CurrencyInterface $cur)
    {
        return in_array($cur->code(), static::allCurrencies());
    }

}
