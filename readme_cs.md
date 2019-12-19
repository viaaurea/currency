# Balicek sluzieb na prevody medzi menami - VA\Currency

> 💿 `composer require viaaurea/currency`

Balicek na porovnavanie a agregacie penaznych hodnot medzi roznymi menami a na konverziu medzi menami.

Balicek nesluzi na formatovanie.


## Currency Service

Sluzba vie prevadzat hodnotu penazi z jednej meny na druhu podla kurzu.
Kurzy dodava sluzba `StaticExchange`, obecne implementacia `ExchangeRateProviderInterface`.


### Inicializacia

CNB udava kurz *priamo*:
```php
// domacou menou je CZK
$exchangeRates = [
	'EUR' => [30, 1],
	'USD' => [20, 1],
	'HUF' => [8.5, 100], // kurz HUF je udavany v stovkach forintov
	'JPY' => [20, 100], // kurz JPY je udavany v stovkach jenov
	'XBT' => [50, 0.001], // kurz je udavany v micro-Bitcoin
];
$exchange = new StaticExchange('CZK', $exchangeRates, StaticExchange::RATE_DIRECT);
$cservice = new CurrencyService($exchange);
```

Pozor, pre Eura je kurz vacsinou udavany **opacne** (*nepriamo*):
```php
// domacou menou je EUR
$exchangeRates = [
	'CZK' => [30, 1],
	'USD' => [1.2, 1],
	'HUF' => [300, 1],
	'JPY' => [10000, 100], // predpoklad, ze sa kurz JPY udava v stovkach jenov
	'XBT' => [0.5, 0.001], // kurz je udavany v micro-Bitcoin
];
$exchange = new StaticExchange('EUR', $exchangeRates, StaticExchange::RATE_INDIRECT);
$cservice = new CurrencyService($exchange);
```

Uvadzanie kurzu:
1. *priame*
	- hodnota je uvedena v domacej mene
	- hodnota vyjadruje mnozstvo jednotiek v domacej mene na jednu jednotu cudzej meny
	- napr: CZK, USD
2. *nepriame*
	- hodnota je uvedena v cudzej mene
	- hodnota vyjadruje mnozstvo jednotiek cudzej meny na jednu jednotku domacej meny
	- napr: EUR

> :bulb:
>
> `CurrencyService` umoznuje zadat mnozstvo jednotiek, na ktore sa kurz vztahuje.
>
> Toto sa vyuziva pri vysokom rozdiele medzi menami, napr pre HUF, JPY, XBT a pod..


### Prevody

```php
$inEUR = new Money(100, 'EUR');
$inCZK = $cservice->exchange($inEUR, 'CZK');  // prevod 100 EUR na CZK
```

> :bulb:
>
> Namiesto stringu s kodom meny je vzdy mozne pouzit objekt `Currency`


### Porovnavanie

Sluzba podporuje porovnavanie penazi v roznych menach.

```php
$inEUR = new Money(100, 'EUR');
$inCZK = new Money(3000, 'CZK');

$cservice->diff($inEUR, $inCZK); //  ~ ( $inCZK - $inEU )

// Agregacie:  min, max, sum, avg
$cservice->avg([$inEUR, $inCZK, ...]);

// Porovnavanie:   equal, notEqual, greaterThan, greaterThanOrEqualTo, lessThan, lessThanOrEqualTo, inRange
// + skratky       eq, neq, gt, gte, lt, lte
$cservice->equal($inEUR, $inCZK); // ~ ( $inCZK == $inEU )
```

> :bulb:
>
> Do kazdeho volania metod `CurrencyService` je mozne pridat parametre, ktore su dalej poslane do volania `ExchangeRateProviderInterface::getExchangeRate`.
>
> Takto je mozne parametrizovat ziskanie kurzu, napriklad pre ziskanie historikeho zaznamu pre porovnanie/prevod meny v minulosti.


## Exchange Rate Provider

Je sluzbou dodavajucou kurzy pre `CurrencyService` implementujucou `ExchangeRateProviderInterface`.

Zatial existuje jedina staticka implementacia `StaticExchange`, ktora dostane staticky kurzovy listok.

Obecne je vsak mozne implementovat sluzby, ktore ziskavaju kurzy z DB, alebo z nejakeho externeho API a suschopne ziskavat aj kurzy z minulosti.


## Money

Je objekt zapuzdrujuci obnos penazi, hodnotu. Sklada sa vzdy z ciselnej hodnoty a meny.

```php
$money = new Money(100, CurrencyIsoCodes::EUR)
$money = new Money(100, new Currency(CurrencyIsoCodes::EUR))

$money->amount();            // 100
$money->currency();          // Currency objekt
$money->currency()->code();  // "EUR"
```

> Pozor - objekt je *immutable*.


## Currency

```php
$currency = new Currency(CurrencyIsoCodes::EUR);

$currency->code();     // "EUR"
```

> Pozor - objekt je *immutable*.



## DI rozsirenie (NEON)

Registracia:
```
extensions:
    currency: VA\Currency\Bridges\Nette\DI\CurrencyExtension
```

Kurzy pro CZK:
```neon
currency:
	reference: CZK     # domaca mena
	direct: yes        # kurz je zadany v domacej mene (reference)
	rates:
		EUR: 26        #   1 EUR stoji 26 CZK
		USD: 24
		JPY: [20, 100] # 100 JPY stoji 20 CZK
```

Kurzy pro EUR:
```neon
currency:
	reference: EUR       # domaca mena; mena, ku ktorej sa vztahuje kurz
	direct: no           # kurz je zadany v cudzej mene
	rates:
		CZK:
			rate: 3.701
			amount: 100
		USD: 0.89 # ==rate, amount: 1 (default)
		JPY: [0.1977, 1000] # alternativny zapis k zapisu pre CZK
```


## Tipy

Pre ziskanie nazvov krajin, kodov mien, znakov mien, SVG vlajok krajin a dalsich nezmyslov odporucam pouzit balicek [rinvex/country](https://github.com/rinvex/country) alebo zoznamy prelozene do roznych locales [umpirsky/currency-list](https://github.com/umpirsky/currency-list).


