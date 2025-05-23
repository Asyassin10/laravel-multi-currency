<?php

namespace YassineAs\MultiCurrency\Facades;

use Illuminate\Support\Facades\Facade;

class Currency extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'YassineAs\MultiCurrency\Services\CurrencyService';
    }
}
