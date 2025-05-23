<?php

namespace YassineAs\MultiCurrency\Services;

use YassineAs\MultiCurrency\Models\ExchangeRate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    public function getRate($fromCurrency, $toCurrency)
    {
        if ($fromCurrency === $toCurrency) {
            return 1.0;
        }

        // Try to get from cache first
        $cacheKey = "exchange_rate_{$fromCurrency}_{$toCurrency}";
        
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // Try to get from database
        $dbRate = ExchangeRate::forPair($fromCurrency, $toCurrency)
                              ->valid()
                              ->first();

        if ($dbRate) {
            Cache::put($cacheKey, $dbRate->rate, config('multi-currency.cache_duration'));
            return $dbRate->rate;
        }

        // Fetch from external API
        $rate = $this->fetchFromApi($fromCurrency, $toCurrency);
        
        if ($rate) {
            // Store in database
            ExchangeRate::updateOrCreate(
                [
                    'base_currency' => $fromCurrency,
                    'target_currency' => $toCurrency,
                ],
                [
                    'rate' => $rate,
                    'expires_at' => now()->addSeconds(config('multi-currency.cache_duration')),
                ]
            );

            // Store in cache
            Cache::put($cacheKey, $rate, config('multi-currency.cache_duration'));
            
            return $rate;
        }

        Log::error("Failed to fetch exchange rate for {$fromCurrency} to {$toCurrency}");
        return 1.0; // Fallback
    }

    protected function fetchFromApi($fromCurrency, $toCurrency)
    {
        $provider = config('multi-currency.exchange_rate_provider');
        $config = config("multi-currency.providers.{$provider}");

        try {
            switch ($provider) {
                case 'exchangerate-api':
                    return $this->fetchFromExchangeRateApi($fromCurrency, $toCurrency, $config);
                
                case 'currencyapi':
                    return $this->fetchFromCurrencyApi($fromCurrency, $toCurrency, $config);
                
                default:
                    throw new \Exception("Unsupported provider: {$provider}");
            }
        } catch (\Exception $e) {
            Log::error("Exchange rate API error: " . $e->getMessage());
            return null;
        }
    }

    protected function fetchFromExchangeRateApi($fromCurrency, $toCurrency, $config)
    {
        $url = str_replace('{base}', $fromCurrency, $config['url']);
        $response = Http::get($url);

        if ($response->successful()) {
            $data = $response->json();
            return $data['rates'][$toCurrency] ?? null;
        }

        return null;
    }

    protected function fetchFromCurrencyApi($fromCurrency, $toCurrency, $config)
    {
        $response = Http::get($config['url'], [
            'apikey' => $config['api_key'],
            'base_currency' => $fromCurrency,
            'currencies' => $toCurrency,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data['data'][$toCurrency]['value'] ?? null;
        }

        return null;
    }

    public function refreshRates($baseCurrency = null)
    {
        $baseCurrency = $baseCurrency ?: config('multi-currency.default_currency');
        $currencies = array_keys(config('multi-currency.supported_currencies'));

        foreach ($currencies as $currency) {
            if ($currency !== $baseCurrency) {
                $this->getRate($baseCurrency, $currency);
            }
        }
    }
}
