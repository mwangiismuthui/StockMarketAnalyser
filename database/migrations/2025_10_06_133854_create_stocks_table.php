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
        Schema::create('stocks', function (Blueprint $table) {
             $table->id();
            $table->foreignId('stock_exchange_id')->constrained()->cascadeOnDelete();
            $table->string('ticker')->unique()->comment('e.g. "nase/KCB"');
            $table->string('name')->comment('e.g. "KCB Group PLC"');
            $table->string('industry')->nullable();
            $table->unsignedBigInteger('employees')->nullable();
            $table->integer('founded')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
