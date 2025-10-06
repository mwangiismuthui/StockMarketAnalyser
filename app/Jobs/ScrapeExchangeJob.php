<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\StockScrapers\ScraperManager;
use App\Models\{StockExchange, Stock, StockMetric};
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ScrapeExchangeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public StockExchange $exchange) {}

    public function handle()
    {
        Log::info("Starting scrape for exchange: {$this->exchange->name} ({$this->exchange->code})");
        app(ScraperManager::class)->scrape($this->exchange);
    }
}
