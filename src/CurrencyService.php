<?php


namespace VA\Currency;

use VA\Currency\CurrencyInterface as C;
use VA\Currency\MoneyInterface as M;


/**
 * Currency Service provides facility to exchange currency and compare and create money objects.
 *
 * @copyright Via Aurea, s.r.o.
 */
class CurrencyService implements CurrencyServiceInterface, ExchangeServiceInterface
{
    /** @var ExchangeRateProviderInterface */
    protected $exchange = null;

    /** @var array */
    protected $exchangeArgs = [];


    public function __construct(ExchangeRateProviderInterface $exchange = null)
    {
        $exchange !== null && $this->setExchangeRateProvider($exchange);
    }


#   ++-----------------------------------------------------------------------++
#   ||   Factory                                                             ||
#   ++-----------------------------------------------------------------------++


    public function create($amount, $currency): M
    {
        return new Money($amount, $currency);
    }


#   ++-----------------------------------------------------------------------++
#   ||   Exchange                                                            ||
#   ++-----------------------------------------------------------------------++


    /**
     * @param MoneyInterface           $money    Money value to convert
     * @param CurrencyInterface|string $currency The target currency
     * @param mixed                    ...$args
     * @return MoneyInterface
     */
    public function exchange(M $money, $currency, ...$args): M
    {
        if (!$currency instanceof CurrencyInterface) {
            $currency = new Currency($currency);
        }
        if ($money->currency()->code() === $currency->code()) {
            return $money;
        }
        if (MathHelper::eq($money->amount(), 0)) {
            $amount = 0;
        } else {
            $exchangeRate = $this->getExchangeRate($currency, $money->currency(), ...$args);
            $amount = MathHelper::mul($money->amount(), $exchangeRate); // base_amount * exchange_rate
        }
        return $this->create($amount, $currency);
    }


    /**
     * Alias for exchange method.
     * Using exchange() is preferred.
     */
    public function convert(M $money, $currency, ...$args): M
    {
        return $this->exchange($money, $currency, ...$args);
    }


#   ++-----------------------------------------------------------------------++
#   ||   Aggregation methods                                                 ||
#   ++-----------------------------------------------------------------------++


    public function max($list, ...$args): M
    {
        list(, $max) = $this->minMax($list, ...$args);
        return $max;
    }


    public function min($list, ...$args): M
    {
        list($min,) = $this->minMax($list, ...$args);
        return $min;
    }


    public function sum($list, $currency = null, ...$args): M
    {
        $sum = 0;
        if ($currency !== null && !$currency instanceof C) {
            $currency = new Currency($currency);
        }
        foreach ($list as $money) {
            if ($currency === null) {
                $currency = $money->currency();
            }
            $x = $this->xchg($money, $currency, ...$args);
            $sum = MathHelper::add($sum, $x->amount());
        }
        return $this->create($sum, $currency);
    }


    public function avg($list, $currency = null, ...$args): M
    {
        $sum = $this->sum($list, $currency, ...$args);
        return $this->create(MathHelper::div($sum->amount(), count($list)), $sum->currency());
    }


#   ++-----------------------------------------------------------------------++
#   ||   Comparison methods                                                  ||
#   ++-----------------------------------------------------------------------++


    public function equal(M $first, M $second, ...$args): bool
    {
        return MathHelper::eq($first->amount(), $this->xchg($second, $first->currency(), ...$args)->amount());
    }


    public function notEqual(M $first, M $second, ...$args): bool
    {
        return !$this->equal($first, $second, ...$args);
    }


    public function lessThan(M $first, M $second, ...$args): bool
    {
        return MathHelper::lt($first->amount(), $this->xchg($second, $first->currency(), ...$args)->amount());
    }


    public function lessThanOrEqualTo(M $first, M $second, ...$args): bool
    {
        return MathHelper::lte($first->amount(), $this->xchg($second, $first->currency(), ...$args)->amount());
    }


    public function greaterThan(M $first, M $second, ...$args): bool
    {
        return MathHelper::gt($first->amount(), $this->xchg($second, $first->currency(), ...$args)->amount());
    }


    public function greaterThanOrEqualTo(M $first, M $second, ...$args): bool
    {
        return MathHelper::gte($first->amount(), $this->xchg($second, $first->currency(), ...$args)->amount());
    }


    /**
     * $second - $first.
     */
    public function diff(M $first, M $second, ...$args): M
    {
        return $this->create(
            MathHelper::sub($this->xchg($second, $first->currency(), ...$args)->amount(), $first->amount()),
            $first->currency()
        );
    }


    /**
     * Is in interval <$from, $to> ?
     *
     * @param MoneyInterface $money
     * @param MoneyInterface $from
     * @param MoneyInterface $to
     * @param bool           $eqFrom use operators <= >= or < >  for "from" comparison
     * @param bool           $eqTo   use operators <= >= or < >  for "to"   comparison
     * @return bool
     */
    public function inRange(M $money, M $from, M $to, $eqFrom = true, $eqTo = true, ...$args): bool
    {
        $frConv = $this->xchg($from, $money->currency(), ...$args);
        $toConv = $this->xchg($to, $money->currency(), ...$args);
        $frPass = $eqFrom ? MathHelper::lte($frConv->amount(), $money->amount()) : MathHelper::lt($frConv->amount(), $money->amount());
        $totoPass = $eqTo ? MathHelper::gte($toConv->amount(), $money->amount()) : MathHelper::gt($toConv->amount(), $money->amount());
        return $frPass && $totoPass;
    }


    /**
     * Alias for equal method.
     */
    public function eq(M $first, M $second, ...$args): bool
    {
        return $this->equal($first, $second, ...$args);
    }


    /**
     * Alias for notEqual method.
     */
    public function neq(M $first, M $second, ...$args): bool
    {
        return $this->notEqual($first, $second, ...$args);
    }


    /**
     * Alias for lessThan method.
     */
    public function lt(M $first, M $second, ...$args): bool
    {
        return $this->lessThan($first, $second, ...$args);
    }


    /**
     * Alias for lessThanOrEqualTo method.
     */
    public function lte(M $first, M $second, ...$args): bool
    {
        return $this->lessThanOrEqualTo($first, $second, ...$args);
    }


    /**
     * Alias for greaterThan method.
     */
    public function gt(M $first, M $second, ...$args): bool
    {
        return $this->greaterThan($first, $second, ...$args);
    }


    /**
     * Alias for greaterThanOrEqualTo method.
     */
    public function gte(M $first, M $second, ...$args): bool
    {
        return $this->greaterThanOrEqualTo($first, $second, ...$args);
    }


#   ++-----------------------------------------------------------------------++
#   ||   Configuration methods                                               ||
#   ++-----------------------------------------------------------------------++


    public function getExchangeRateProvider(): ExchangeRateProviderInterface
    {
        return $this->exchange;
    }


    public function setExchangeRateProvider(ExchangeRateProviderInterface $exchange = null): self
    {
        $this->exchange = $exchange;
        return $this;
    }


    public function setDefaultExchangeArgs(...$args): self
    {
        $this->exchangeArgs = $args;
        return $this;
    }


    /**
     * Get default exchange arguments.
     *
     * This may be useful in cases you want to alter the default behaviour your exchange rate provider.
     *
     * @return array
     */
    public function getDefaultExchangeArgs(): array
    {
        return $this->exchangeArgs;
    }


#   ++-----------------------------------------------------------------------++
#   ||   Internal methods                                                    ||
#   ++-----------------------------------------------------------------------++


    /**
     * Internal exchange. When no additional arguments are provided, arguments returned by getDefaultExchangeArgs() are used.
     *
     * @return MoneyInterface
     */
    protected function xchg(M $money, C $currency, ...$args): M
    {
        return $this->exchange($money, $currency, ...$args ?? $this->getDefaultExchangeArgs());
    }


    protected function getExchangeRate(C $to, C $from, ...$args)
    {
        return $this->getExchangeRateProvider()->getExchangeRate($to, $from, ...$args);
    }


    protected function minMax($list, ...$args): array
    {
        $min = $max = null;
        foreach ($list as $money) {
            if ($min === null || $this->lessThan($money, $min, ...$args)) {
                $min = $money;
            }
            if ($max === null || $this->greaterThan($money, $max, ...$args)) {
                $max = $money;
            }
        }
        return [$min, $max];
    }


#   ++-----------------------------------------------------------------------++
#   ||   Deprecated                                                          ||
#   ++-----------------------------------------------------------------------++


    /**
     * @deprecated
     */
    public function setDefaultExchangeConfig(...$args)
    {
        return $this->setDefaultExchangeArgs(...$args);
    }


    /**
     * @deprecated
     */
    protected function getDefaultExchangeConfig()
    {
        return $this->exchangeArgs;
    }

}
