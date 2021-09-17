<?php

declare(strict_types=1);

namespace VA\Currency;

use InvalidArgumentException;
use JsonSerializable;

/**
 * Money - immutable value object
 *
 * @copyright Via Aurea, s.r.o.
 */
final class Money implements MoneyInterface, JsonSerializable
{
    /** @var int|float */
    private $amount;
    private CurrencyInterface $currency;

    /**
     * Money value object constructor.
     *
     * @param int|double $amount
     * @param CurrencyInterface|string $currency
     */
    public function __construct($amount, $currency)
    {
        if (!is_numeric($amount)) {
            throw new InvalidArgumentException("Value / amount for money must be a number.");
        }
        if (is_string($currency)) {
            $currency = new Currency($currency);
        }
        if (!$currency instanceof CurrencyInterface) {
            throw new InvalidArgumentException(sprintf("Currency must be an instance of %s or a string.", CurrencyInterface::class));
        }
        $this->amount = $amount + 0; // cast to integer or double
        $this->currency = $currency;
    }

    /**
     * Get the amount of the value object.
     *
     * @return int|double
     */
    public function amount()
    {
        return $this->amount;
    }

    /**
     * Get the currency of the value object.
     *
     * @return CurrencyInterface
     */
    public function currency(): CurrencyInterface
    {
        return $this->currency;
    }

    /**
     * Note: type-casting to string is not supposed to be used for formatting.
     */
    function __toString(): string
    {
        return $this->amount() . ' ' . $this->currency()->code();
    }

    public function jsonSerialize(): array
    {
        return [
            'amount' => $this->amount(),
            'currency' => $this->currency(),
        ];
    }

    /**
     * Allows for constructs like `Money::USD(100)`.
     */
    public static function __callStatic($name, $arguments)
    {
        return new static($arguments[0], $name);
    }
}
