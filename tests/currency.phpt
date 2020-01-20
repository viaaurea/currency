<?php

/**
 * Currency unit Test
 *
 * @copyright Via Aurea, s.r.o.
 */


namespace VA\Currency\Tests;

use InvalidArgumentException;
use Tester\Assert;
use VA\Currency\Currency;

require_once('bootstrap.php');

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ Tests ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

currencyConstructor();
currencyGetters();
currencyStaticFactory();
currencyComparisons();
currencyToString();


// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ Test definitions ~~~~~~~~~~~~~~~~~~~~~~~~~~


function currencyComparisons()
{
    $a1 = new Currency('XRP');
    $a2 = new Currency('XRP');

    $b = new Currency('XBT');

    Assert::equal($a1, $a2);
    Assert::notEqual($a1, $b);
    Assert::notEqual($a2, $b);

    Assert::notSame($a1, $a2);
    Assert::notSame($a1, $b);
}


function currencyToString()
{
    $codeA = 'XBT';
    $currencyA = new Currency($codeA);
    Assert::same($codeA, (string)$currencyA);

    $codeB = 'EUR';
    $currencyB = new Currency($codeB);
    Assert::same($codeB, (string)$currencyB);

    $codeC = 1234;
    $currencyC = new Currency($codeC);
    Assert::same((string)$codeC, (string)$currencyC);
}


function currencyGetters()
{
    $code = 'XBT';
    $currency = new Currency($code);

    Assert::same($code, $currency->code());
}


function currencyConstructor()
{
    Assert::noError(function () {
        $code = 'XBT';
        new Currency($code);
    });

    Assert::noError(function () {
        new Currency(11);
    });

    Assert::exception(function () {
        new Currency('');
    }, InvalidArgumentException::class, 'Currency code can not be empty string!');
}


function currencyStaticFactory()
{
    $c = Currency::FOO();
    Assert::type(Currency::class, $c);
    Assert::same('FOO', $c->code());

    $c = Currency::foobar();
    Assert::type(Currency::class, $c);
    Assert::same('foobar', $c->code());
}
