<?php


namespace VA\Currency;

use VA\Currency\Exceptions\SetupException;


/**
 * Exchange rate provider with static pre-configured rates.
 *
 * Note: This provider does not distinguish between sell and buy rates, it is used for approximate conversions.
 *
 * Reference currency is the base or local currency the exchange rates of foreign currencies are related to.
 *
 * The exchange rates represent exchange rates of foreign currencies.
 * Rates are defined as an associative array indexed by foreign currency codes,
 * containing either numeric rates (in which case the amount equals to 1)
 * or pairs [rate, amount] (the exchange rate and the amount of foreign currency units the rate refers to).
 * E.g. rate of Yen to USD could be 0.009 for one Yen (amount==1), or 9.0 for 100 Yen (amount==100) as well.
 *
 * The rates can be directly or indirectly quoted, which must be specified as well.
 *
 * Note:
 * Direct quotation is where the cost of one unit of foreign currency is given in units of local/base currency,
 * whereas indirect quotation is where the cost of one unit of local/base currency is given in units of foreign currency.
 *
 * @copyright Via Aurea, s.r.o.
 */
class StaticExchange implements ExchangeRateProviderInterface
{
    const
        CFGKEY_RATE = 0,
        CFGKEY_AMOUNT = 1;

    /** @var CurrencyInterface */
    protected $reference;

    /** @var string enum: direct / indirect quotation */
    protected $rateType;

    /** @var array */
    protected $rates = [];


    /**
     * @param string|CurrencyInterface $referenceCurrency
     * @param array                    $exchangeRates             exchange rates indexed by currency codes, see class description
     * @param string                   $exchangeRateQuotationType enum ExchangeRateProviderInterface::RATE_*
     */
    public function __construct($referenceCurrency, array $exchangeRates, string $exchangeRateQuotationType)
    {
        $this->reference = $cur = $referenceCurrency instanceof CurrencyInterface ? $referenceCurrency : new Currency($referenceCurrency);
        unset($exchangeRates[$cur->code()]);
        $this->rates = $exchangeRates;
        if ($exchangeRateQuotationType !== self::RATE_INDIRECT && $exchangeRateQuotationType !== self::RATE_DIRECT) {
            throw new SetupException(sprintf('The rate quotation type must be either %s::RATE_DIRECT or %s::RATE_INDIRECT.', ExchangeRateProviderInterface::class, ExchangeRateProviderInterface::class));
        }
        $this->rateType = $exchangeRateQuotationType;
    }


    /**
     * Returns exchange rate for conversion from $from currency to $target currency.
     * If $from is not specified, the reference currency is used by default.
     *
     * This method returns the rate in indirect quotation.
     *
     * @param CurrencyInterface $target
     * @param CurrencyInterface $from
     * @return float|int
     */
    public function getExchangeRate(CurrencyInterface $target, CurrencyInterface $from = null)
    {
        $getter = function (CurrencyInterface $target) {
            [$rate, $amount, $type] = $this->getRawExchangeRateSetup($target->code());

            // Calculate & return unit exchange rate (so that $amount==1).
            // Direct quotation rate needs to be inverted (1/$exchangeRate) into indirect quotation rate.
            return $type === self::RATE_DIRECT ?
                MathHelper::div($amount, $rate) : //  direct quotation   -->  1 / ( $rate / $amount ) === $amount / $rate
                MathHelper::mul($rate, $amount);  //  indirect quotation -->  $rate * $amount
        };
        $referenceCurrency = $this->getReferenceCurrency();
        try {
            return ExchangeHelper::calculateRate($referenceCurrency, $target, $from ?? $referenceCurrency, $getter);
        } catch (SetupException $e) {
            throw new SetupException(sprintf('Exchange from %s: %s', ($from ?? $referenceCurrency)->code(), $e->getMessage()), null, $e);
        }
    }


    /**
     * Get list of currencies with exchange rate set.
     *
     * @return array
     */
    public function getAvailableCurrencies(): array
    {
        return array_merge([$this->getReferenceCurrency()->code()], array_keys($this->rates));
    }


    /**
     * Discover whether a currency has exchange rate set.
     *
     * @param CurrencyInterface|string $currency
     * @return bool
     */
    public function isAvailable($currency): bool
    {
        $code = $currency instanceof CurrencyInterface ? $currency->code() : (string)$currency;
        return $code === $this->getReferenceCurrency()->code() || ($this->rates[$code] ?? false);
    }


    /**
     * Reference/base/local currency.
     * All exchange rates are defined relative to this currency.
     *
     * @return CurrencyInterface
     */
    public function getReferenceCurrency(): CurrencyInterface
    {
        return $this->reference;
    }


    /**
     * Returns an array containing raw rate, amount and quotation type, as configured.
     *
     * @internal
     *
     * @param string $code
     * @return array [<rate>, <amount>, <type>]
     */
    public function getRawExchangeRateSetup(string $code): array
    {
        $rateSetup = $this->rates[$code] ?? null;
        if ($rateSetup === null) {
            throw new SetupException(sprintf('Exchange rate for %s is not set.', $code));
        }

        if (is_array($rateSetup)) {
            if (!isset($rateSetup[self::CFGKEY_RATE])) {
                throw new SetupException(sprintf('Exchange rate for %s is not set.', $code));
            }
            if (!isset($rateSetup[self::CFGKEY_AMOUNT])) {
                throw new SetupException(sprintf('Exchange amount for %s is not set.', $code));
            }
            $rawRate = $rateSetup[self::CFGKEY_RATE];
            $rawAmount = $rateSetup[self::CFGKEY_AMOUNT];
        } else {
            $rawRate = $rateSetup;
            $rawAmount = 1;
        }

        try {
            $rate = MathHelper::number($rawRate);
            $amount = MathHelper::number($rawAmount);
        } catch (SetupException $e) {
            throw new SetupException(sprintf('Exchange rate for %s is incorrectly set.', $code), null, $e);
        }

        if ($rate <= 0 || $amount <= 0) {
            throw new SetupException(sprintf('Exchange rate for %s is incorrect, both rate and amount must be positive numbers, (rate: %s, amount: %s) given.', $code, $rate, $amount));
        }
        return [$rate + 0, $amount + 0, $this->rateType];
    }

}
