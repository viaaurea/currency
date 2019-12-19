<?php


namespace VA\Currency;


/**
 * Helper class encapsulating common exchange rate provider logic.
 * Useful when creating own exchange rate providers.
 *
 * @copyright Via Aurea, s.r.o.
 */
class ExchangeHelper
{

    /**
     * Calculate INDIRECT exchange rate.
     *
     * The getter function must return the INDIRECT quotation exchange rate for the given currency.
     * The getter has the following signature
     * function( CurrencyInterface ): int|float
     *
     * @param CurrencyInterface $reference
     * @param CurrencyInterface $target
     * @param CurrencyInterface $base
     * @param callable $indirectRateGetter a getter function specific to the particular provider
     * @return float|int|mixed
     */
    static function calculateRate(
        CurrencyInterface $reference,
        CurrencyInterface $target,
        CurrencyInterface $base,
        callable $indirectRateGetter
    ) {
        // the rate is always 1 for same currencies
        if ($target->code() === $base->code()) {
            return 1;
        }

        // konverzia je presne podla nastavenia, konvertujeme z referencnej meny na inu
        if ($base->code() === $reference->code()) {
            return call_user_func($indirectRateGetter, $target);
        }

        // konverzia je z inej meny na referencnu, musime kurz "otocit" 1/rate
        if ($target->code() === $reference->code()) {
            return MathHelper::div(1, static::calculateRate($reference, $base, $reference, $indirectRateGetter));
        }

        // konverzia medzi menami, ktore nie su referencne ani jedna - musia byt konvertovane na/z referencnu
        $baseToRef = static::calculateRate($reference, $reference, $base, $indirectRateGetter);
        $refToTarget = static::calculateRate($reference, $target, $reference, $indirectRateGetter);
        return MathHelper::mul($baseToRef, $refToTarget);
    }

}