<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_metrics', function (Blueprint $table) {
              $table->id();
            $table->foreignId('stock_id')->constrained()->cascadeOnDelete();

            $table->timestamp('recorded_at')->comment('Timestamp when the data was recorded');

            $table->decimal('price', 15, 4)->nullable();
            $table->decimal('change', 10, 4)->nullable();
            $table->unsignedBigInteger('market_cap')->nullable();
            $table->unsignedBigInteger('revenue')->nullable();

            // Total Returns
            $table->decimal('tr1m', 10, 3)->nullable();
            $table->decimal('tr6m', 10, 3)->nullable();
            $table->decimal('trYTD', 10, 3)->nullable();
            $table->decimal('tr1y', 10, 3)->nullable();
            $table->decimal('tr5y', 10, 3)->nullable();
            $table->decimal('tr10y', 10, 3)->nullable();

            // Dividend info
            $table->decimal('dps', 10, 3)->nullable();
            $table->decimal('dividend_yield', 10, 4)->nullable();
            $table->decimal('dividend_growth', 10, 4)->nullable();
            $table->date('ex_div_date')->nullable();
            $table->decimal('payout_ratio', 10, 4)->nullable();
            $table->string('payout_frequency')->nullable();

            // Volume & 52-week ranges
            $table->unsignedBigInteger('volume')->nullable();
            $table->decimal('low_52', 10, 3)->nullable();
            $table->decimal('low_52_ch', 10, 4)->nullable();
            $table->decimal('high_52', 10, 3)->nullable();
            $table->decimal('high_52_ch', 10, 4)->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_metrics');
    }
};
