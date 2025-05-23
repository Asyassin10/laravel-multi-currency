<?php

namespace YassineAs\MultiCurrency;

use Illuminate\Support\ServiceProvider;
use YassineAs\MultiCurrency\Services\CurrencyService;
use YassineAs\MultiCurrency\Services\ExchangeRateService;
use YassineAs\MultiCurrency\Services\GeolocationService;
use YassineAs\MultiCurrency\Http\Middleware\SetCurrencyMiddleware;
use YassineAs\MultiCurrency\Console\Commands\RefreshExchangeRatesCommand;

class MultiCurrencyServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/multi-currency.php', 'multi-currency');
        
        $this->app->singleton(CurrencyService::class);
        $this->app->singleton(ExchangeRateService::class);
        $this->app->singleton(GeolocationService::class);
    }

    public function boot()
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/../config/multi-currency.php' => config_path('multi-currency.php'),
        ], 'config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'migrations');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'multi-currency');

        // Publish views
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/multi-currency'),
        ], 'views');

        // Register middleware
        $router = $this->app['router'];
        $router->aliasMiddleware('currency', SetCurrencyMiddleware::class);

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                RefreshExchangeRatesCommand::class,
            ]);
        }

        // Register Blade directives
        $this->registerBladeDirectives();
    }

    protected function registerBladeDirectives()
    {
        \Blade::directive('currency', function ($expression) {
            return "<?php echo app('YassineAs\\\\MultiCurrency\\\\Services\\\\CurrencyService')->format({$expression}); ?>";
        });

        \Blade::directive('currencySymbol', function () {
            return "<?php echo app('YassineAs\\\\MultiCurrency\\\\Services\\\\CurrencyService')->getCurrentSymbol(); ?>";
        });
    }
}
