<?php

declare(strict_types=1);

namespace VA\Currency;

use InvalidArgumentException;
use JsonSerializable;

/**
 * Currency - immutable value object
 *
 * @copyright Via Aurea, s.r.o.
 */
final class Currency implements CurrencyInterface, JsonSerializable
{
    private string $code;

    /**
     * Currency constructor.
     *
     * @param string|CurrencyInterface $code
     */
    public function __construct($code)
    {
        if ($code instanceof CurrencyInterface) {
            $code = $code->code();
        } else {
            $code = (string)$code;
        }
        if ($code === '') {
            throw new InvalidArgumentException('Currency code can not be empty string!');
        }
        $this->code = $code;
    }

    /**
     * Currency code.
     * Preferably an ISO code, but it's up to you.
     *
     * @return string
     */
    public function code(): string
    {
        return $this->code;
    }

    public function __toString(): string
    {
        return $this->code();
    }

    public function jsonSerialize(): string
    {
        return $this->code();
    }

    /**
     * Allows for constructs like `Currency::USD()`.
     */
    public static function __callStatic($name, $arguments)
    {
        return new static($name);
    }
}
