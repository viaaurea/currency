<?php

namespace VA\Currency\Bridges\Nette\DI;

use InvalidArgumentException;
use Nette\DI\CompilerExtension;
use Nette\DI\InvalidConfigurationException;
use VA\Currency\Currency;
use VA\Currency\CurrencyService;
use VA\Currency\ExchangeRateProviderInterface;
use VA\Currency\StaticExchange;

/**
 * DI Extension for nette/di.
 *
 * Nette DI package required:
 * `composer require nette/di`
 *
 *
 * Example:
 *    ```neon
 *    currency:
 *        reference: EUR            # local currency / reference currency to which the rates relate
 *        direct:    yes            # are the rates defined in local (direct) or foreign (indirect) currency ?
 *        rates:
 *            CZK:                  # full notation
 *                rate: 3.701
 *                amount: 100
 *            JPY: [0.1977, 1000]   # [<rate>, <amount>], alternative full notation
 *            USD: 0.89             # <rate>, short notation, amount defaults to 1
 *    ```
 *
 * @copyright Via Aurea, s.r.o.
 */
class CurrencyExtension extends CompilerExtension
{
    const
        CURRENCY_CONFIG_DEFAULT = "reference",
        CURRENCY_CONFIG_CURRENCIES = "rates",
        CURRENCY_CONFIG_DIRECT_RATE = "direct";
    const
        RATIO = "rate",
        RATIO_INDEX = StaticExchange::CFGKEY_RATE,
        AMOUNT = "amount",
        AMOUNT_INDEX = StaticExchange::CFGKEY_AMOUNT;

    /** @var array */
    private $defaults = [
        self::CURRENCY_CONFIG_DEFAULT => null,
        self::CURRENCY_CONFIG_DIRECT_RATE => null,
        self::CURRENCY_CONFIG_CURRENCIES => [],
    ];


    public function loadConfiguration()
    {
        $config = (array)$this->validateConfig($this->defaults, $this->config);

        if (!isset($config[self::CURRENCY_CONFIG_DEFAULT]) || $config[self::CURRENCY_CONFIG_DEFAULT] === '') {
            throw new InvalidConfigurationException(sprintf('Default currency not set. Please provide the default by setting the %s option in configuration.', self::CURRENCY_CONFIG_DEFAULT));
        }
        if (!isset($config[self::CURRENCY_CONFIG_DIRECT_RATE]) || $config[self::CURRENCY_CONFIG_DIRECT_RATE] === '') {
            throw new InvalidConfigurationException(sprintf('Exchange rate type not set. Please provide the type by setting the %s option in configuration.', self::CURRENCY_CONFIG_DIRECT_RATE));
        }

        $default = new Currency($config[self::CURRENCY_CONFIG_DEFAULT]);
        $currencies = $this->loadCurrencies($config[self::CURRENCY_CONFIG_CURRENCIES]);
        $rateType = $config[self::CURRENCY_CONFIG_DIRECT_RATE] ? ExchangeRateProviderInterface::RATE_DIRECT : ExchangeRateProviderInterface::RATE_INDIRECT;

        $builder = $this->getContainerBuilder();
        $exchange = $builder->addDefinition($this->prefix('exchange'))
            ->setClass(StaticExchange::class)
            ->setArguments([$default, $currencies, $rateType]);

        $builder->addDefinition($this->prefix('currency'))
            ->setClass(CurrencyService::class)
            ->setArguments([$exchange]);

        if ($this->name === 'currency') {
            $builder->addAlias('currency', $this->prefix('currency'));
        }
    }


    /**
     * Vrací pole s kurzy
     *
     * @param array $aCurrencies
     * @return array
     */
    protected function loadCurrencies(array $aCurrencies)
    {
        $currencies = [];

        foreach ($aCurrencies as $code => $rate) {
            $amount = 1; // default value

            if (is_array($rate)) {
                if (array_key_exists(self::RATIO, $rate)) {
                    $ratio = $rate[self::RATIO];
                } elseif (array_key_exists(self::RATIO_INDEX, $rate)) {
                    $ratio = $rate[self::RATIO_INDEX];
                } else {
                    throw new InvalidArgumentException("Měna : {$code} nemá vyplněno kurz (rate).");
                }
                if (array_key_exists(self::AMOUNT, $rate)) {
                    $amount = $rate[self::AMOUNT];
                } elseif (array_key_exists(self::AMOUNT_INDEX, $rate)) {
                    $amount = $rate[self::AMOUNT_INDEX];
                }
            } else {
                // pokud je zadefinován jen kurz (rate)
                if (empty($rate)) {
                    throw new InvalidArgumentException("Není definován kurz u {$code}.");
                }
                $ratio = $rate;
            }

            $currencies[$code] = [self::RATIO_INDEX => $ratio + 0, self::AMOUNT_INDEX => $amount];
        }

        return $currencies;
    }
}
