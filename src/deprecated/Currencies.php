<?php


namespace VA\Currency;

use ReflectionClass;


/**
 * @deprecated this class/interface was used for internal purposes, not suitable for public domain
 */
class Currencies implements CurrencyIsoCodes
{
    /**
     * Currency symbols.
     *
     * @var array of strings
     */
    protected static $symbols = [
        self::AUD => '$',
        self::CAD => '$',
        self::CZK => 'Kč',
        self::EUR => '€',
        self::GBP => '£',
        self::HRK => 'kn',
        self::HUF => 'Ft',
        self::ISK => 'kr',
        self::JPY => '¥',
        self::NOK => 'kr',
        self::PLN => 'zł',
        self::RUB => '₽',
        self::UAH => '₴',
        self::USD => '$',
    ];

    /**
     * Default currency formatting pattern.
     *
     * @var string
     */
    protected static $defaultFormatPattern = '# $';

    /**
     * Currency formatting patterns.
     * $ represents the symbol part
     * # represents the value  part
     *
     * @var array array of string patterns
     */
    protected static $formatPatterns = [
        self::USD => '$#',
    ];


    /**
     * Return a symbol for a currency, if present.
     *
     *
     * @param string|CurrencyInterface $currency
     * @return string|NULL
     */
    public static function symbol($currency)
    {
        $code = $currency instanceof CurrencyInterface ? $currency->code() : $currency;
        return static::$symbols[$code] ?? null;
    }


    /**
     * Return format pattern for a currency, if present.
     *
     * In the returned format pattern,
     *  - $ represents the symbol part
     *  - # represents the value  part
     *
     *
     * @param string|CurrencyInterface $currency
     * @return string|NULL format pattern
     */
    public static function formatPattern($currency)
    {
        $code = $currency instanceof CurrencyInterface ? $currency->code() : $currency;
        return static::$formatPatterns[$code] ?? null;
    }


    /**
     * Return the default format pattern for currencies without a specific one.
     *
     * In the returned format pattern,
     *  - $ represents the symbol part
     *  - # represents the value  part
     *
     *
     * @return string format pattern
     */
    public static function defaultFormatPattern(): string
    {
        return static::$defaultFormatPattern;
    }


    /**
     * List currencies.
     *
     *
     * @return array
     */
    public static function listAll(): array
    {
        $class = new ReflectionClass(CurrencyIsoCodes::class);
        return $class->getConstants();
    }


    /**
     * Check if currency is valid.
     *
     *
     * @param CurrencyInterface $cur
     * @return bool
     */
    public static function isValidCurrency(CurrencyInterface $cur): bool
    {
        return in_array($cur->code(), static::listAll());
    }


    public static function symbols(): array
    {
        return static::$symbols;
    }


    public static function formatPatterns(): array
    {
        return static::$formatPatterns;
    }

}
