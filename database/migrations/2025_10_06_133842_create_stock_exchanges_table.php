<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_exchanges', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('e.g. "nase" or "nse"');
            $table->string('name')->comment('e.g. "Nairobi Securities Exchange"');
            $table->string('country')->nullable()->comment('e.g. "Kenya"');
            $table->string('currency')->nullable();
            $table->integer('stocks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_exchanges');
    }
};
