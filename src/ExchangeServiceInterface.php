<?php

declare(strict_types=1);

namespace VA\Currency;

/**
 * Interface for services providing currency exchange facility.
 *
 * @copyright Via Aurea, s.r.o.
 */
interface ExchangeServiceInterface
{
    /**
     * Changes given money to a new currency.
     *
     * @param MoneyInterface $money Money value object to exchange FROM
     * @param CurrencyInterface|string $targetCurrency currency to exchange the Money object TO
     * @return MoneyInterface Money value object in the target currency
     */
    public function exchange(MoneyInterface $money, $targetCurrency): MoneyInterface;
}
