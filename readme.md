# Currency

[![Test Suite](https://github.com/viaaurea/currency/actions/workflows/php-test.yml/badge.svg)](https://github.com/viaaurea/currency/actions/workflows/php-test.yml)
![PHP from Packagist](https://img.shields.io/packagist/php-v/viaaurea/currency)
[![Powered by](https://img.shields.io/badge/by-Via%20Aurea-orange)](https://www.viaaurea.cz/)

**Cross-currency money comparison and exchange tool.** Framework agnostic.

> 💿 `composer require viaaurea/currency`
>
> 📖 [Czech/Slovak readme version](readme_cs.md).

Use this package to do comparison arithmetic or aggregations on values in different currencies or exchange money in one currency to another.

```php
// resolve the service / get an instance
$cs = $container->get(VA\Currency\CurrencyService::class);

// create a money value object (3 equivalent ways)
$cs->create(100, 'USD');
new Money(100, 'USD');
Money::USD(100);

// exchange / conversion
$valueInUsd = Money::USD(100);
$valueInEur = $cs->exchange($valueInUsd, 'EUR');
$valueInEur->amount();

// comparison arithmetic
// < <= > >= == !=
$cs->greaterThan($valueInUsd, $valueInForeignCurrency); // or $cs->gt( ... )

// diff mixed currencies
$diff = $cs->diff($valueInEur, $valueInForeignCurrency);

// aggregate an array of Money objects with unknown or mixed currencies:
// sum, average, max or min value
$money = [ $valueInEur, $valueInUsd, $valueInForeignCurrency, ... ];
$sum = $cs->sum($money);
$max = $cs->max($money);
$min = $cs->min($money);
$avg = $cs->avg($money);
```

> 💡\
> This package is meant as a **lightweight tool** to exchange, compare and aggregate money value objects.\
> For a fully fledged robust solution consider using [Money for PHP](https://moneyphp.org/en/stable/).


## Currency Service

`CurrencyService` is the main service class used for conversions and arithmetics. Its main dependency is an implementation of an **exchange rate provider**.\
During comparison operations or aggregations between money objects with different currencies exchange to one of the currencies must happen.\
The provider provides exchange rates for these conversions as well as for explicit conversions using `CurrencyService::exchange` method.

Currently only one implementation is delivered with this package - the `StaticExchange`, however, it is trivial to provide your own implementation according to your needs (database, third party API fetches etc.).


## Configuration

The exchange rates are defined and passed to the `StaticExchange` class, which is then used by the `CurrencyService`. You can also provide your own exchange rate provider simply by implementing the `ExchangeRateProviderInterface` interface.

The rates are defined in relation to a reference/base/local currency.

For _directly_ quoted reference currencies (USD, CAD, JPY, CZK, ...):
```php
$container->register(CurrencyService::class, function(){
    $rates = [
        'EUR' => 0.9,
        'JPY' => 100,
        'AUD' => 1.5,
    ];
    $provider = new StaticExchange('USD', $rates, StaticExchange::RATE_DIRECT);
    return new CurrencyService($provider);
});
```

For _indirectly_ quoted reference currencies (EUR, GBP, AUD, ...):
```php
$container->register(CurrencyService::class, function(){
    $rates = [
        'USD' => 1.1,
        'JPY' => 120,
        'AUD' => 1.6,
    ];
    $provider = new StaticExchange('EUR', $rates, StaticExchange::RATE_INDIRECT);
    return new CurrencyService($provider);
});
```

> 💡 You are not required to use a service container, in which case just assign the instance to a variable...

It is possible to specify amount of units with the rate. It can be used for currencies where the difference to the base currency is very high (certain African currencies, Bitcoin, etc.). To do that, pass in an array with the rate and amount:
```php
$rates = [
    // rate for Bitcoin may be specified in micro-Bitcoins:
    'XBT' => [0.5, 0.001],
];
```


## Usage

See the examples at the beginning of this file above and see the [source code of `CurrencyService` class](src/CurrencyService.php) for full list of available methods.


## Fine-grain exchanges

The `exchange` method, as well as every comparison or aggregation method of `CurrencyService` accepts a **variadic list of arguments** that is passed down to the calls to underlying exchange rate provider. This can be leveraged to enable calculation of historic currency values, buy/sell/middle rates and so on.

Examples:
```php
$cs->exchange(
    $cs->create(100, 'EUR'), 
    'USD', 
    Carbon::parse('last saturday')
);

$cs->diff(
    $cs->create(100, 'EUR'),
    $cs->create(100, 'USD'),
    Carbon::parse('2017-11-23'),
    'buy'
);
```

To use this functionality, a custom provider needs to be implemented, example:
```php
class DatabaseRateProvider implements ExchangeRateProviderInterface {

    function getExchangeRate(
        Currency $target, 
        Currency $from, 
        DateTimeInterface $at, 
        string $direction = 'middle'
    ){
        // your logic here
        // return exchange rate at a given point in time and for the specified direction
    }

}
```

> Tip: use the `ExchangeHelper` class to easily implement a custom provider.


## Nette DI extension

Contains an extension for the Nette framework. See the [Czech/Slovak readme version](readme_cs.md).


## Contributions

... are welcome.
