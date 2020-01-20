<?php

namespace VA\Currency;

/**
 * ExchangeRateProviderInterface
 *
 * @copyright Via Aurea, s.r.o.
 */
interface ExchangeRateProviderInterface
{
    const
        RATE_DIRECT = 'd',
        RATE_INDIRECT = 'i';

    /**
     * Returns the exchange rate for a given currency.
     * Note that this rate is always in INDIRECT quotation.
     *
     * $rate = $exchangeProvider->getExchangeRate( $foreignCurrency );
     * $valueInForeign = $valueInBaseCurrency * $rate;
     *
     * In the most basic implementation conversions from the base currency are assumed:
     *
     * $rate = $exchangeService->getExchangeRate(new Currency('EUR')); // assumes USD is the base currency of the provider
     * $hundredUsd = new Money(100, 'USD');
     * $inEur = $hundredUsd->amount() * $rate;
     *
     * Most of the time the implementation will allow conversion between multiple currencies,
     * when a pair of to-from currencies will be used,
     * possibly other arguments that influence the rate (e.g. date when the currency was in effect):
     *
     * $date = CarbonImmutable::parse('last friday');
     * $rate = $exchangeService->getExchangeRate($to = new Currency('EUR'), $from = new Currency('USD'), $date);
     * $hundredUsd = new Money(100, 'USD');
     * $inEur = $hundredUsd->amount() * $rate;
     *
     * @param CurrencyInterface $currency
     * @param ...$args
     * @return mixed
     */
    public function getExchangeRate(CurrencyInterface $currency /*, CurrencyInterface $from, ...$args */);
}
