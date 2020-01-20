# Changelog

## v2.0

This update aims for simpler public interface, improved reusability and extensibility.

All `CurrencyService` methods should work without change, care should be taken when custom implementations or extensions of classes listed below were created. Most notably, `ExchangeRateProviderInterface` has been changed and internal working of `StaticExchange` class has been reimplemented and `CurrencyService::exchange` needed to be updated accordingly.

- PHP 7.1 required
- deprecated all currency and currency symbol lists (see `src/deprecated`).
- `CurrencyService::exchange` now uses variadic parameters
    - `ExchangeConfig` deprecated and removed from all the methods where it was used. Variadic parameters are used instead.
    - `StaticExchange::getExchangeRate` also uses variadic parameters
- `CurrencyService::setDefaultExchangeConfig` has been removed, `CurrencyService::setDefaultExchangeArgs` should be used instead, for variadic parameters
- `CurrencyService::getDefaultExchangeArgs` has been made public
- `ExchangeRateProviderInterface::getExchangeRateType` interface method and `StaticExchange::getExchangeRateType` implementation method removed
    - `ExchangeRateProviderInterface::getExchangeRate` must now always return exchange rate quoted _indirectly_
    - the implementation using this feature in `CurrencyService::exchange` has been updated accordingly
- type of `ExchangeRateProviderInterface::RATE_*` constants have been changed to string, the constant values have been changed too (should not matter if used correctly, i.e. using the constants and not the values)
- `CurrencyExtension` DI extension moved to a more specific namespace
- `CurrencyExtension` now requires default currency and exchange rate type to be set (defaults have been removed)

