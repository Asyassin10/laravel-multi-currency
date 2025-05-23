<?php

namespace YassineAs\MultiCurrency\Services;

use YassineAs\MultiCurrency\Services\ExchangeRateService;
use YassineAs\MultiCurrency\Services\GeolocationService;

class CurrencyService
{
    protected $exchangeRateService;
    protected $geolocationService;
    protected $currentCurrency;

    public function __construct(ExchangeRateService $exchangeRateService, GeolocationService $geolocationService)
    {
        $this->exchangeRateService = $exchangeRateService;
        $this->geolocationService = $geolocationService;
        $this->initializeCurrency();
    }

    protected function initializeCurrency()
    {
        // Check session first
        if (session()->has(config('multi-currency.session_key'))) {
            $this->currentCurrency = session(config('multi-currency.session_key'));
            return;
        }

        // Check cookie
        if (request()->hasCookie(config('multi-currency.cookie_name'))) {
            $this->currentCurrency = request()->cookie(config('multi-currency.cookie_name'));
            session([config('multi-currency.session_key') => $this->currentCurrency]);
            return;
        }

        // Auto-detect based on IP if enabled
        if (config('multi-currency.auto_detect_currency')) {
            $detectedCurrency = $this->geolocationService->detectCurrency();
            if ($detectedCurrency && $this->isSupported($detectedCurrency)) {
                $this->currentCurrency = $detectedCurrency;
                $this->setCurrency($detectedCurrency);
                return;
            }
        }

        // Fallback to default
        $this->currentCurrency = config('multi-currency.default_currency');
    }

    public function getCurrentCurrency()
    {
        return $this->currentCurrency;
    }

    public function getCurrentSymbol()
    {
        $currencies = config('multi-currency.supported_currencies');
        return $currencies[$this->currentCurrency]['symbol'] ?? '$';
    }

    public function getCurrentName()
    {
        $currencies = config('multi-currency.supported_currencies');
        return $currencies[$this->currentCurrency]['name'] ?? 'Unknown';
    }

    public function setCurrency($currency)
    {
        if (!$this->isSupported($currency)) {
            throw new \InvalidArgumentException("Currency {$currency} is not supported");
        }

        $this->currentCurrency = $currency;
        session([config('multi-currency.session_key') => $currency]);
        
        // Set cookie
        cookie()->queue(
            config('multi-currency.cookie_name'),
            $currency,
            config('multi-currency.cookie_duration')
        );
    }

    public function convert($amount, $fromCurrency = null, $toCurrency = null)
    {
        $fromCurrency = $fromCurrency ?: config('multi-currency.default_currency');
        $toCurrency = $toCurrency ?: $this->currentCurrency;

        if ($fromCurrency === $toCurrency) {
            return $amount;
        }

        $rate = $this->exchangeRateService->getRate($fromCurrency, $toCurrency);
        return $amount * $rate;
    }

    public function format($amount, $currency = null, $fromCurrency = null)
    {
        $currency = $currency ?: $this->currentCurrency;
        $convertedAmount = $this->convert($amount, $fromCurrency, $currency);
        
        $symbol = config("multi-currency.supported_currencies.{$currency}.symbol", '$');
        
        return $symbol . number_format($convertedAmount, 2);
    }

    public function getSupportedCurrencies()
    {
        return config('multi-currency.supported_currencies');
    }

    public function isSupported($currency)
    {
        return array_key_exists($currency, config('multi-currency.supported_currencies'));
    }
}
