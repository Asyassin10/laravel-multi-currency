<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExchangeRatesTable extends Migration
{
    public function up()
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->string('base_currency', 3);
            $table->string('target_currency', 3);
            $table->decimal('rate', 10, 6);
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->unique(['base_currency', 'target_currency']);
            $table->index(['base_currency', 'target_currency', 'expires_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('exchange_rates');
    }
}
