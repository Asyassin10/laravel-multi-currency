<?php

namespace YassineAs\MultiCurrency\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class GeolocationService
{
    protected $currencyMap = [
        'US' => 'USD',
        'GB' => 'GBP',
        'DE' => 'EUR',
        'FR' => 'EUR',
        'IT' => 'EUR',
        'ES' => 'EUR',
        'SA' => 'SAR',
        'AE' => 'AED',
        'EG' => 'EGP',
    ];

    public function detectCurrency($ip = null)
    {
        $ip = $ip ?: request()->ip();
        
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return config('multi-currency.default_currency');
        }

        $cacheKey = "geolocation_{$ip}";
        
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $countryCode = $this->getCountryCode($ip);
            $currency = $this->currencyMap[$countryCode] ?? config('multi-currency.default_currency');
            
            Cache::put($cacheKey, $currency, 3600); // Cache for 1 hour
            
            return $currency;
        } catch (\Exception $e) {
            return config('multi-currency.default_currency');
        }
    }

    protected function getCountryCode($ip)
    {
        // Using ip-api.com (free service)
        $response = Http::get("http://ip-api.com/json/{$ip}");
        
        if ($response->successful()) {
            $data = $response->json();
            return $data['countryCode'] ?? null;
        }

        return null;
    }
}
