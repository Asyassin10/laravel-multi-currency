<?php

namespace YassineAs\MultiCurrency\Console\Commands;

use Illuminate\Console\Command;
use YassineAs\MultiCurrency\Services\ExchangeRateService;

class RefreshExchangeRatesCommand extends Command
{
    protected $signature = 'currency:refresh-rates {--base=}';
    protected $description = 'Refresh exchange rates from external API';

    protected $exchangeRateService;

    public function __construct(ExchangeRateService $exchangeRateService)
    {
        parent::__construct();
        $this->exchangeRateService = $exchangeRateService;
    }

    public function handle()
    {
        $baseCurrency = $this->option('base') ?: config('multi-currency.default_currency');
        
        $this->info("Refreshing exchange rates with base currency: {$baseCurrency}");
        
        $this->exchangeRateService->refreshRates($baseCurrency);
        
        $this->info('Exchange rates refreshed successfully!');
    }
}
