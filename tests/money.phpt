<?php

/**
 * Money unit Test
 *
 * @copyright Via Aurea, s.r.o.
 */


namespace VA\Currency\Tests;

use InvalidArgumentException;
use Tester\Assert;
use VA\Currency\Currency;
use VA\Currency\Money;

require_once('bootstrap.php');

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ Tests ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

moneyConstructor();
moneyGetters();
moneyStaticFactory();
moneyToString();
moneyJson();
moneyComparisons();


// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ Test definitions ~~~~~~~~~~~~~~~~~~~~~~~~~~


function moneyComparisons()
{
    $a1 = new Money(100, 'XRP');
    $a2 = new Money(100, 'XRP');

    $b = new Money(200, 'XBT');
    $c = new Money(100, 'EUR');

    Assert::equal($a1, $a2);
    Assert::notEqual($a1, $b);
    Assert::notEqual($a2, $b);
    Assert::notEqual($a1, $c);
    Assert::notEqual($a2, $c);
    Assert::notEqual($b, $c);

    Assert::notSame($a1, $a2);
    Assert::notSame($a1, $b);
    Assert::notSame($a1, $c);
}


function moneyConstructor()
{
    Assert::exception(function () {
        new Money('fero', 'mrkva');
    }, InvalidArgumentException::class, 'Value / amount for money must be a number.');

    Assert::exception(function () {
        new Money(0, '');
    }, InvalidArgumentException::class, 'Currency code can not be empty string!');

    Assert::noError(function () {
        new Money(0, 'foo');
        new Money(1, 'foo');
        new Money(1.0, 'foo');
        new Money(-1.0, 'foo');
        new Money('-1.0', 'foo');
        new Money(0b01, 'foo');
        new Money(0xabc, 'foo');
    });
}


function moneyGetters()
{
    $currencyA = new Currency('XBT');
    $amountA = 100;
    $moneyA = new Money($amountA, $currencyA);
    Assert::same($currencyA, $moneyA->currency());
    Assert::same($amountA, $moneyA->amount());

    $currencyB = new Currency('XBT');
    $amountB = -1.0;
    $moneyB = new Money($amountB, $currencyB);
    Assert::same($currencyB, $moneyB->currency());
    Assert::same($amountB, $moneyB->amount());

    $currencyC = new Currency('XBT');
    $amountC = '-112.45';
    $moneyC = new Money($amountC, $currencyC);
    Assert::same($currencyC, $moneyC->currency());
    Assert::same((double)$amountC, $moneyC->amount());
}


function moneyToString()
{
    Assert::same('10 XBT', (string)new Money(10, 'XBT'));
}

function moneyJson()
{
    Assert::equal([
        'amount' => 10,
        'currency' => 'XBT',
    ], json_decode(json_encode(new Money(10, 'XBT')), JSON_OBJECT_AS_ARRAY));
    Assert::equal([
        'amount' => 1.3,
        'currency' => 'EUR',
    ], json_decode(json_encode(new Money(1.3, 'EUR')), JSON_OBJECT_AS_ARRAY));
}


function moneyStaticFactory()
{
    $m = Money::FOO(0.0);
    Assert::type(Money::class, $m);
    Assert::same(0.0, $m->amount());
    Assert::same('FOO', $m->currency()->code());

    $m = Money::foobar(100);
    Assert::type(Money::class, $m);
    Assert::same(100, $m->amount());
    Assert::same('foobar', $m->currency()->code());
}
