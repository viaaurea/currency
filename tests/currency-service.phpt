<?php

/**
 * CurrencyService unit Test
 *
 * @copyright Via Aurea, s.r.o.
 */


namespace VA\Currency\Tests;

use Tester\Assert;
use VA\Currency\CurrencyService;
use VA\Currency\CurrencyServiceInterface;
use VA\Currency\Exceptions\SetupException;
use VA\Currency\ExchangeServiceInterface;
use VA\Currency\Money;
use VA\Currency\StaticExchange;

require_once('bootstrap.php');


// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ Tests ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~


service(czk());
service(eur());

comparisons();
exchange();
walkz();


// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ Test definitions ~~~~~~~~~~~~~~~~~~~~~~~~~~


function service($exchange)
{
    Assert::type(CurrencyService::class, $exchange);
    Assert::type(CurrencyServiceInterface::class, $exchange);
    Assert::type(ExchangeServiceInterface::class, $exchange);
}


function comparisons()
{
    $x = czk();

    $m = function ($a, $c) {
        return new Money($a, $c);
    };

    Assert::true($x->equal($m(100, 'CZK'), $m(100, 'CZK')));
    Assert::true($x->equal($m(-100, 'CZK'), $m(-100, 'CZK')));
    Assert::true($x->equal($m(100, 'CZK'), $m(100.0, 'CZK')));
    Assert::false($x->equal($m(100, 'CZK'), $m(100, 'EUR')));
    Assert::true($x->equal($m(100, 'EUR'), $m(100, 'EUR')));
    Assert::false($x->equal($m(100, 'USD'), $m(100, 'EUR')));
    Assert::false($x->equal($m(100, 'CZK'), $m(200, 'CZK')));

    Assert::false($x->lessThan($m(100, 'CZK'), $m(100, 'CZK')));
    Assert::true($x->lessThan($m(100, 'CZK'), $m(200, 'CZK')));
    Assert::true($x->lessThan($m(-100, 'CZK'), $m(200, 'CZK')));
    Assert::true($x->lessThan($m(100, 'CZK'), $m(100, 'EUR')));
    Assert::false($x->lessThan($m(100, 'EUR'), $m(100, 'CZK')));

    Assert::true($x->greaterThan($m(200, 'CZK'), $m(100, 'CZK')));
    Assert::false($x->greaterThan($m(100, 'CZK'), $m(100, 'CZK')));
    Assert::false($x->greaterThan($m(100, 'CZK'), $m(200, 'CZK')));
    Assert::false($x->greaterThan($m(-100, 'CZK'), $m(200, 'CZK')));
    Assert::false($x->greaterThan($m(100, 'CZK'), $m(100, 'EUR')));
    Assert::true($x->greaterThan($m(100, 'EUR'), $m(100, 'CZK')));

    Assert::true($x->lessThanOrEqualTo($m(100, 'CZK'), $m(100, 'CZK')));
    Assert::true($x->lessThanOrEqualTo($m(100, 'CZK'), $m(200, 'CZK')));
    Assert::true($x->lessThanOrEqualTo($m(-100, 'CZK'), $m(200, 'CZK')));
    Assert::true($x->lessThanOrEqualTo($m(100, 'CZK'), $m(100, 'EUR')));
    Assert::false($x->lessThanOrEqualTo($m(100, 'EUR'), $m(100, 'CZK')));

    Assert::true($x->greaterThanOrEqualTo($m(200, 'CZK'), $m(100, 'CZK')));
    Assert::true($x->greaterThanOrEqualTo($m(100, 'CZK'), $m(100, 'CZK')));
    Assert::false($x->greaterThanOrEqualTo($m(100, 'CZK'), $m(200, 'CZK')));
    Assert::false($x->greaterThanOrEqualTo($m(-100, 'CZK'), $m(200, 'CZK')));
    Assert::false($x->greaterThanOrEqualTo($m(100, 'CZK'), $m(100, 'EUR')));
    Assert::true($x->greaterThanOrEqualTo($m(100, 'EUR'), $m(100, 'CZK')));

    // diff without exchange
    Assert::true($x->equal($m(100, 'CZK'), $x->diff($m(100, 'CZK'), $m(200, 'CZK'))));
    Assert::true($x->equal($m(-100, 'CZK'), $x->diff($m(200, 'CZK'), $m(100, 'CZK'))));
    Assert::true($x->equal($m(0, 'CZK'), $x->diff($m(100, 'CZK'), $m(100, 'CZK'))));

    // diff with exchange
    Assert::true($x->greaterThan(
        $x->diff($m(100, 'CZK'), $m(100, 'EUR')), //
        $m(0, 'CZK')
    ));
    Assert::true($x->lessThan(
        $m(0, 'CZK'), //
        $x->diff($m(100, 'CZK'), $m(100, 'EUR'))
    ));
    Assert::true($x->equal(
        $m(0, 'CZK'), //
        $x->diff($m(100, 'EUR'), $m(100, 'EUR'))
    ));
}


function exchange()
{
    $x = czk();
    $y = eur();

    $m = function ($a, $c) {
        return new Money($a, $c);
    };

    // sanity checks
    Assert::true(0 == $x->exchange($m(0, 'EUR'), 'CZK')->amount());
    Assert::true(30 == $x->exchange($m(1, 'EUR'), 'CZK')->amount());
    Assert::true(1 == $x->exchange($m(30, 'CZK'), 'EUR')->amount());
    Assert::true(-20.0 == $x->exchange($m(-1, 'USD'), 'CZK')->amount());
    Assert::true(1 == $x->exchange($m(20, 'CZK'), 'USD')->amount());
    Assert::true(-8.5 == $x->exchange($m(-100, 'HUF'), 'CZK')->amount());
    Assert::true(10000 == $x->exchange($m(850, 'CZK'), 'HUF')->amount());

    // sanity checks
    Assert::true(0 == $y->exchange($m(0, 'EUR'), 'CZK')->amount());
    Assert::true(30 == $y->exchange($m(1, 'EUR'), 'CZK')->amount());
    Assert::true(1 == $y->exchange($m(30, 'CZK'), 'EUR')->amount());
    Assert::true(1.2 == $y->exchange($m(1, 'EUR'), 'USD')->amount());
    Assert::true(1 == $y->exchange($m(1.2, 'USD'), 'EUR')->amount());

    // sanity checks
    Assert::exception(function () use ($x, $m) {
        $x->exchange($m(1, 'EUR'), 'FOO')->amount();
    }, SetupException::class);

    /* CZK - direct */

    $czk3000 = $x->exchange($m(100, 'EUR'), 'CZK'); // 100 EUR in CZK
    Assert::same('CZK', $czk3000->currency()->code()); // the result currency should be CZK
    Assert::true(3000 == $czk3000->amount()); // the result should be 3000

    $usd150 = $x->exchange($m(3000, 'CZK'), 'USD');
    Assert::same('USD', $usd150->currency()->code());
    Assert::true(150 == $usd150->amount());

    $usdEur = $x->exchange($m(100, 'EUR'), 'USD'); // USD to EUR, reference currency is CZK
    Assert::same('USD', $usdEur->currency()->code());
    Assert::true(150 == $usdEur->amount());

    $eurUsd = $x->exchange($m(150, 'USD'), 'EUR'); // USD to EUR, reference currency is CZK
    Assert::same('EUR', $eurUsd->currency()->code());
    Assert::true(100 == $eurUsd->amount());

    $huf = $x->exchange($m(1000, 'HUF'), 'CZK');
    Assert::same('CZK', $huf->currency()->code());
    Assert::equal(85.0, $huf->amount());

    /* EUR - indirect */

    $czk3000y = $y->exchange($m(100, 'EUR'), 'CZK'); // 100 EUR in CZK
    Assert::same('CZK', $czk3000y->currency()->code()); // the result currency should be CZK
    Assert::true(3000 == $czk3000y->amount()); // the result should be 3000

    $eur100y = $y->exchange($m(3000, 'CZK'), 'EUR');
    Assert::same('EUR', $eur100y->currency()->code());
    Assert::true(100 == $eur100y->amount());

    $eurToUsd = $y->exchange($m(100, 'EUR'), 'USD'); // USD to EUR, reference currency is EUR
    Assert::same('USD', $eurToUsd->currency()->code());
    Assert::true(120 == $eurToUsd->amount());

    $czkToUsd = $y->exchange($m(3000, 'CZK'), 'USD');
    Assert::same('USD', $czkToUsd->currency()->code());
    Assert::true(120 == $czkToUsd->amount());

    Assert::equal(1.0, $x->exchange($m(50000, 'CZK'), 'XBT')->amount());
    Assert::equal(50000.0, $x->exchange($m(1, 'XBT'), 'CZK')->amount());
    Assert::equal(1.0, $y->exchange($m(2000, 'EUR'), 'XBT')->amount());
    Assert::equal(2000.0, $y->exchange($m(1, 'XBT'), 'EUR')->amount());

    /**/
    // diff with exchange
    $czk = $x->exchange($m(100, 'EUR'), 'CZK')->amount(); // 100 EUR in CZK
    $diff = $x->diff($m(100, 'CZK'), $m(100, 'EUR')); // substract 100 CZK from 100 EUR
    Assert::same('CZK', $diff->currency()->code()); // the result currency should be CZK
    Assert::equal($czk - 100, $diff->amount()); // there should be 3000 - 100 CZK = 2900 CZK

    /**/
    // foo test - just a simple optimization, where when exchanging 0 value,
    // the exchange rate is not even fetched, thus no "missing exchange rate" exception
    Assert::true(0 == $x->exchange(new Money(0, 'FOO'), 'BAR')->amount());
}


function walkz()
{
    $x = eur();

    $m = function ($a, $c) {
        return new Money($a, $c);
    };

    $foo = [
        $m(100, 'CZK'),
        $m(100, 'EUR'),
        $m(100, 'HUF'),
        $m(1000, 'CZK'),
    ];

    Assert::equal($m(100, 'EUR'), $x->max($foo));
    Assert::equal($m(100, 'HUF'), $x->min($foo));
    Assert::true($x->equal($m(4110, 'CZK'), $x->sum($foo)));
    Assert::true($x->equal($m(4110, 'CZK'), $x->sum($foo, 'EUR')));
    Assert::true($x->equal($m(137, 'EUR'), $x->sum($foo, 'EUR')));
    Assert::equal($m(137.0, 'EUR'), $x->sum($foo, 'EUR'));
    Assert::true($x->equal($m(4110 / 4, 'CZK'), $x->avg($foo)));
}


function czk()
{
    $exchangeRates = [
        'EUR' => [30, 1],
        'USD' => [20, 1],
        'HUF' => [8.5, 100], // kurz HUF je udavany v stovkach forintov
        'JPY' => [20, 100], // kurz JPY je udavany v stovkach jenov
        'XBT' => [50, 0.001], // kurz je udavany v micro-Bitcoin
    ];

    $exchange = new StaticExchange('CZK', $exchangeRates, StaticExchange::RATE_DIRECT);
    return new CurrencyService($exchange);
}


function eur()
{
    $exchangeRates = [
        'CZK' => [30, 1],
        'USD' => [1.2, 1],
        'HUF' => [300, 1],
        'JPY' => [10000, 100], // predpoklad, ze sa kurz JPY udava v stovkach jenov
        'XBT' => [0.5, 0.001], // kurz je udavany v micro-Bitcoin
    ];

    $exchange = new StaticExchange('EUR', $exchangeRates, StaticExchange::RATE_INDIRECT);
    return new CurrencyService($exchange);
}
