<?php

namespace YassineAs\MultiCurrency\Http\Middleware;

use Closure;
use YassineAs\MultiCurrency\Services\CurrencyService;

class SetCurrencyMiddleware
{
    protected $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    public function handle($request, Closure $next)
    {
        // Currency is already initialized in the service constructor
        // This middleware can be used for additional logic if needed
        
        return $next($request);
    }
}
