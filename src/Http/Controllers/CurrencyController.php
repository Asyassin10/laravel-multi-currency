<?php

namespace YassineAs\MultiCurrency\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use YassineAs\MultiCurrency\Services\CurrencyService;

class CurrencyController extends Controller
{
    protected $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    public function switch(Request $request)
    {
        $currency = $request->input('currency');
        
        if (!$currency || !$this->currencyService->isSupported($currency)) {
            return response()->json(['error' => 'Invalid currency'], 400);
        }

        $this->currencyService->setCurrency($currency);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'currency' => $currency,
                'symbol' => $this->currencyService->getCurrentSymbol(),
            ]);
        }

        return redirect()->back();
    }

    public function getSupportedCurrencies()
    {
        return response()->json([
            'current' => $this->currencyService->getCurrentCurrency(),
            'currencies' => $this->currencyService->getSupportedCurrencies(),
        ]);
    }
}
