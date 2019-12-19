<?php

/**
 * StaticExchange unit Test
 *
 * @copyright Via Aurea, s.r.o.
 */


namespace VA\Currency\Tests;

use Tester\Assert;
use VA\Currency\Currency;
use VA\Currency\Exceptions\SetupException;
use VA\Currency\ExchangeRateProviderInterface;
use VA\Currency\StaticExchange;

require_once('bootstrap.php');

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ Tests ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

$ratesCZK = [
    'EUR' => 30,
    'USD' => '20',
    'HUF' => [8.5, 100], // kurz HUF je udavany v stovkach forintov
    'JPY' => [20, 100], // kurz JPY je udavany v stovkach jenov
    'XBT' => [50, 0.001], // kurz je udavany v micro-Bitcoin, 1 XBT stoji 50 tisic CZK
];
$ratesEUR = [
    'CZK' => 30,
    'USD' => '1.2',
    'HUF' => [300, 1],
    'JPY' => [0.1, 1000], // predpoklad, ze sa kurz JPY udava v tisickach jenov
    'XBT' => [0.5, 0.001], // kurz je udavany v micro-Bitcoin, 1 XBT stoji 2000 EUR
];

seBase($ratesCZK);
seRatesIndirect($ratesEUR);
seRatesDirect($ratesCZK);


// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ Test definitions ~~~~~~~~~~~~~~~~~~~~~~~~~~

function seBase($rates)
{
    $se1 = new StaticExchange('CZK', $rates, StaticExchange::RATE_DIRECT);
    $se2 = new StaticExchange('EUR', $rates, StaticExchange::RATE_INDIRECT);

    // check raw setup
    Assert::same([20, 1, StaticExchange::RATE_DIRECT], $se1->getRawExchangeRateSetup('USD'));
    Assert::same([20, 1, StaticExchange::RATE_INDIRECT], $se2->getRawExchangeRateSetup('USD'));
    Assert::same([50, 0.001, StaticExchange::RATE_DIRECT], $se1->getRawExchangeRateSetup('XBT'));
    Assert::same([50, 0.001, StaticExchange::RATE_INDIRECT], $se2->getRawExchangeRateSetup('XBT'));
    Assert::same([20, 100, StaticExchange::RATE_DIRECT], $se1->getRawExchangeRateSetup('JPY'));
    Assert::same([20, 100, StaticExchange::RATE_INDIRECT], $se2->getRawExchangeRateSetup('JPY'));
    Assert::exception(function () use ($se1) {
        $se1->getRawExchangeRateSetup('CZK');
    }, SetupException::class);

    Assert::same('CZK', $se1->getReferenceCurrency()->code());
    Assert::same('EUR', $se2->getReferenceCurrency()->code());
    Assert::same(['CZK', 'EUR', 'USD', 'HUF', 'JPY', 'XBT'], $se1->getAvailableCurrencies());
    Assert::same(['EUR', 'USD', 'HUF', 'JPY', 'XBT'], $se2->getAvailableCurrencies());
    Assert::true($se1->isAvailable('CZK'));
    Assert::true($se1->isAvailable('EUR'));
    Assert::true($se1->isAvailable('USD'));
    Assert::true($se1->isAvailable('XBT'));
    Assert::false($se1->isAvailable('FOO'));
    Assert::false($se2->isAvailable('CZK'));
    Assert::true($se2->isAvailable('EUR'));
    Assert::true($se2->isAvailable('USD'));

    Assert::noError(function () use ($se1) {
        $se1->getExchangeRate(new Currency('CZK'));
    });
    Assert::exception(function () use ($se1) {
        $se1->getExchangeRate(new Currency('FOO'));
    }, SetupException::class);

    Assert::exception(function () {
        new StaticExchange('foobar', [], 'foo');
    }, SetupException::class, sprintf('The rate quotation type must be either %s::RATE_DIRECT or %s::RATE_INDIRECT.', ExchangeRateProviderInterface::class, ExchangeRateProviderInterface::class));
}


function seRatesIndirect($ratesEUR)
{
    $se = new StaticExchange('EUR', $ratesEUR, StaticExchange::RATE_INDIRECT);

    $c = function ($cur) {
        return new Currency($cur);
    };

    // no conversion
    Assert::equal(1, $se->getExchangeRate($c('EUR')));
    Assert::same(30, $se->getExchangeRate($c('CZK')));
    Assert::same(300, $se->getExchangeRate($c('HUF')));
    Assert::same(1.2, $se->getExchangeRate($c('USD')));
    Assert::same(100.0, $se->getExchangeRate($c('JPY')));
    Assert::same(0.0005, $se->getExchangeRate($c('XBT')));

    // 1/n conversion (reverse)
    Assert::equal(1, $se->getExchangeRate($c('EUR'), $c('EUR')));
    Assert::equal(1 / 30, $se->getExchangeRate($c('EUR'), $c('CZK'))); // CZK to EUR
    Assert::equal(1 / 300, $se->getExchangeRate($c('EUR'), $c('HUF'))); // HUF to EUR
    Assert::equal(1 / 1.2, $se->getExchangeRate($c('EUR'), $c('USD'))); // USD to EUR
    Assert::equal(0.01, $se->getExchangeRate($c('EUR'), $c('JPY'))); // JPY to EUR

    // conversion between two different currencies
    Assert::equal(0.1, $se->getExchangeRate($c('CZK'), $c('HUF'))); // HUF to CZK
    Assert::equal(10.0, $se->getExchangeRate($c('HUF'), $c('CZK'))); // CZK to HUF
    Assert::equal((1 / 30) * 1.2, $se->getExchangeRate($c('USD'), $c('CZK'))); // CZK to USD
    Assert::equal(0.01 * 1.2, $se->getExchangeRate($c('USD'), $c('JPY'))); // JPY to USD
}


function seRatesDirect($ratesCZK)
{
    $se = new StaticExchange('CZK', $ratesCZK, StaticExchange::RATE_DIRECT);

    $c = function ($cur) {
        return new Currency($cur);
    };

    // no conversion
    Assert::same(1, $se->getExchangeRate($c('CZK')));
    Assert::equal(1 / 30, $se->getExchangeRate($c('EUR')));
    Assert::equal(1 / (8.5 / 100), $se->getExchangeRate($c('HUF')));
    Assert::equal(1 / 20, $se->getExchangeRate($c('USD')));
    Assert::equal(1 / (20 / 100), (float)$se->getExchangeRate($c('JPY')));
    Assert::equal(1 / (50 / 0.001), (float)$se->getExchangeRate($c('XBT')));

    // 1/n conversion (reverse)
    Assert::equal(1, $se->getExchangeRate($c('CZK'), $c('CZK')));
    Assert::equal(30.0, (float)$se->getExchangeRate($c('CZK'), $c('EUR')));
    Assert::equal(8.5 / 100, (float)$se->getExchangeRate($c('CZK'), $c('HUF'))); // HUF to CZK
    Assert::equal(20.0, (float)$se->getExchangeRate($c('CZK'), $c('USD'))); // USD to CZK
    Assert::equal(20 / 100, (float)$se->getExchangeRate($c('CZK'), $c('JPY'))); // JPY to CZK
    Assert::equal(50 / 0.001, (float)$se->getExchangeRate($c('CZK'), $c('XBT')));

    // conversion between two different currencies
    Assert::equal(1 / (30 / 20), $se->getExchangeRate($c('EUR'), $c('USD'))); // USD to EUR
    Assert::equal(1 / (20 / 30), $se->getExchangeRate($c('USD'), $c('EUR'))); // EUR to USD
    Assert::equal(1 / (20.0 / 0.2), $se->getExchangeRate($c('USD'), $c('JPY'))); // JPY to USD
}
