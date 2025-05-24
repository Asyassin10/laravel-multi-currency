# Laravel Multi-Currency Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/yassine-as/laravel-multi-currency.svg?style=flat-square)](https://packagist.org/packages/yassine-as/laravel-multi-currency)
[![Total Downloads](https://img.shields.io/packagist/dt/yassine-as/laravel-multi-currency.svg?style=flat-square)](https://packagist.org/packages/yassine-as/laravel-multi-currency)
[![License](https://img.shields.io/packagist/l/yassine-as/laravel-multi-currency.svg?style=flat-square)](https://packagist.org/packages/yassine-as/laravel-multi-currency)

A comprehensive Laravel package for multi-currency support with automatic conversion, IP-based currency detection, caching, and seamless integration.

## ✨ Features

- 🌍 **Automatic Currency Detection** - Detects user's currency based on IP geolocation
- 💱 **Real-time Exchange Rates** - Fetches rates from multiple providers (ExchangeRate-API, CurrencyAPI)
- ⚡ **Smart Caching** - Database + memory caching to optimize performance
- 🎛️ **Manual Currency Switching** - User-friendly dropdown with AJAX support
- 🍪 **Session & Cookie Support** - Remembers user preferences
- 🎨 **Blade Directives** - Easy integration with `@currency()` and `@currencySymbol`
- 🛡️ **Middleware Support** - Automatic currency detection per request
- 🔧 **Artisan Commands** - Update exchange rates via command line
- 📱 **Responsive UI** - Ready-to-use currency switcher component

## 🚀 Installation

Install the package via Composer:

```bash
composer require yassine-as/laravel-multi-currency
```

### Publish Configuration & Migrations

```bash
# Publish config file
php artisan vendor:publish --provider="YassineAs\MultiCurrency\MultiCurrencyServiceProvider" --tag="config"

# Publish migrations
php artisan vendor:publish --provider="YassineAs\MultiCurrency\MultiCurrencyServiceProvider" --tag="migrations"

# Publish views (optional)
php artisan vendor:publish --provider="YassineAs\MultiCurrency\MultiCurrencyServiceProvider" --tag="views"
```

### Run Migrations

```bash
php artisan migrate
```

## ⚙️ Configuration

Add these variables to your `.env` file:

```env
# Base currency for your products
DEFAULT_CURRENCY=USD

# Exchange rate provider (exchangerate-api or currencyapi)  
EXCHANGE_RATE_PROVIDER=exchangerate-api

# API Keys (get free keys from providers)
EXCHANGE_RATE_API_KEY=your_exchangerate_api_key
CURRENCY_API_KEY=your_currency_api_key

# Auto-detect currency based on user's IP
AUTO_DETECT_CURRENCY=true

# Cache duration in seconds (3600 = 1 hour)
CURRENCY_CACHE_DURATION=3600
```

### Get Free API Keys

**ExchangeRate-API (Recommended):**
1. Visit [exchangerate-api.com](https://exchangerate-api.com)
2. Sign up for free (1,500 requests/month)
3. Copy your API key to `EXCHANGE_RATE_API_KEY`

**CurrencyAPI (Alternative):**
1. Visit [currencyapi.com](https://currencyapi.com)
2. Sign up for free (300 requests/month)
3. Copy your API key to `CURRENCY_API_KEY`

## 📖 Usage

### 1. In Blade Templates

```blade
{{-- Display price with current currency --}}
<p>Price: @currency($product->price)</p>

{{-- Display just the currency symbol --}}
<span>@currencySymbol</span>

{{-- Include the currency switcher dropdown --}}
@include('multi-currency::currency-switcher')

{{-- Convert specific amount --}}
<p>Converted: @currency(100, 'EUR')</p>
```

### 2. In Controllers

```php
<?php

use YassineAs\MultiCurrency\Facades\Currency;

class ProductController extends Controller
{
    public function show(Product $product)
    {
        // Get current currency
        $currentCurrency = Currency::getCurrentCurrency(); // e.g., 'EUR'
        
        // Get currency symbol
        $symbol = Currency::getCurrentSymbol(); // e.g., '€'
        
        // Convert price
        $convertedPrice = Currency::convert($product->price, 'USD', 'EUR');
        
        // Format price with current currency
        $formattedPrice = Currency::format($product->price);
        
        // Set user's currency
        Currency::setCurrency('SAR');
        
        // Get all supported currencies
        $currencies = Currency::getSupportedCurrencies();
        
        return view('products.show', compact('product', 'formattedPrice'));
    }
}
```

### 3. In Models

Add these methods to your Product model:

```php
<?php

class Product extends Model
{
    // Get price in specific currency
    public function getPriceInCurrency($currency = null)
    {
        return app('YassineAs\MultiCurrency\Services\CurrencyService')
               ->convert($this->price, config('multi-currency.default_currency'), $currency);
    }
    
    // Get formatted price with currency symbol
    public function getFormattedPrice($currency = null)
    {
        return app('YassineAs\MultiCurrency\Services\CurrencyService')
               ->format($this->price, $currency);
    }
}
```

### 4. AJAX Currency Switching

The package includes a ready-to-use currency switcher:

```blade
{{-- Add to your layout --}}
@include('multi-currency::currency-switcher')

{{-- Or create custom switcher --}}
<select id="currency-selector">
    @foreach(Currency::getSupportedCurrencies() as $code => $currency)
        <option value="{{ $code }}" {{ Currency::getCurrentCurrency() === $code ? 'selected' : '' }}>
            {{ $currency['symbol'] }} {{ $currency['name'] }}
        </option>
    @endforeach
</select>

<script>
document.getElementById('currency-selector').addEventListener('change', function() {
    fetch('/currency/switch', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ currency: this.value })
    }).then(() => location.reload());
});
</script>
```

## 🎛️ Artisan Commands

### Refresh Exchange Rates

```bash
# Update all exchange rates
php artisan currency:refresh-rates

# Update rates with specific base currency
php artisan currency:refresh-rates --base=EUR
```

### Schedule Automatic Updates

Add to your `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Update exchange rates every hour
    $schedule->command('currency:refresh-rates')->hourly();
}
```

## 🌍 Supported Currencies

The package supports these currencies by default:

| Code | Currency | Symbol |
|------|----------|--------|
| USD  | US Dollar | $ |
| EUR  | Euro | € |
| GBP  | British Pound | £ |
| SAR  | Saudi Riyal | ر.س |
| AED  | UAE Dirham | د.إ |
| EGP  | Egyptian Pound | ج.م |

### Add More Currencies

Edit `config/multi-currency.php`:

```php
'supported_currencies' => [
    'USD' => ['symbol' => '$', 'name' => 'US Dollar'],
    'EUR' => ['symbol' => '€', 'name' => 'Euro'],
    'JPY' => ['symbol' => '¥', 'name' => 'Japanese Yen'],
    'CAD' => ['symbol' => 'C$', 'name' => 'Canadian Dollar'],
    // Add more currencies...
],
```

## 🛣️ API Routes

The package provides these routes:

```php
POST /currency/switch        # Switch currency
GET  /currency/supported     # Get supported currencies
```

## 🔧 Advanced Configuration

### Custom Exchange Rate Provider

Create your own provider:

```php
// In your ExchangeRateService
protected function fetchFromCustomApi($fromCurrency, $toCurrency, $config)
{
    // Implement your custom API logic
    $response = Http::get('https://your-api.com/rates', [
        'from' => $fromCurrency,
        'to' => $toCurrency,
        'key' => $config['api_key']
    ]);
    
    return $response->json()['rate'] ?? null;
}
```

### Custom Geolocation Service

Override the default IP detection:

```php
// In config/multi-currency.php
'custom_geolocation' => true,

// Create your own GeolocationService
class CustomGeolocationService extends GeolocationService
{
    public function detectCurrency($ip = null)
    {
        // Your custom logic
        return 'USD';
    }
}
```

## 🧪 Testing

```bash
# Test currency conversion
Currency::convert(100, 'USD', 'EUR');

# Test formatting
Currency::format(100); // Returns: $100.00

# Test currency detection
app('YassineAs\MultiCurrency\Services\GeolocationService')->detectCurrency();
```

## 🎨 Frontend Integration


<script>
function currencySwitcher() {
    return {
        currency: 'USD',
        currencies: @json(Currency::getSupportedCurrencies()),
        
        async switchCurrency() {
            await fetch('/currency/switch', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ currency: this.currency })
            });
            location.reload();
        }
    }
}
</script>
```

## 🚨 Troubleshooting

### Common Issues

**1. Exchange rates not updating:**
```bash
# Clear cache and refresh rates
php artisan cache:clear
php artisan currency:refresh-rates
```

**2. Currency not detected:**
- Check if `AUTO_DETECT_CURRENCY=true` in `.env`
- Verify IP detection is working (test with different IPs)

**3. API rate limits exceeded:**
- Use caching (increase `CURRENCY_CACHE_DURATION`)
- Consider upgrading your API plan
- Switch to alternative provider

**4. Blade directives not working:**
- Clear view cache: `php artisan view:clear`
- Ensure service provider is registered

## 📝 Changelog

### v1.0.0
- Initial release
- Multi-currency support
- IP-based detection
- Exchange rate caching
- Blade directives
- AJAX currency switching

## 🤝 Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Submit a pull request


## 🙏 Credits

- **Author:** [Yassine AS](https://github.com/Asyassin10)
- **Exchange Rate APIs:** [ExchangeRate-API](https://exchangerate-api.com), [CurrencyAPI](https://currencyapi.com)
- **IP Geolocation:** [ip-api.com](http://ip-api.com)
