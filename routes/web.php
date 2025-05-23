<?php

use YassineAs\MultiCurrency\Http\Controllers\CurrencyController;

Route::group(['prefix' => 'currency', 'as' => 'currency.'], function () {
    Route::post('switch', [CurrencyController::class, 'switch'])->name('switch');
    Route::get('supported', [CurrencyController::class, 'getSupportedCurrencies'])->name('supported');
});
