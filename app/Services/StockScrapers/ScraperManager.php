<?php

namespace App\Services\StockScrapers;

use App\Models\StockExchange;
use Illuminate\Support\Facades\Log;

class ScraperManager
{
 public function scrape(StockExchange $exchange)
    {
        $scraper = new StockAnalysisScraper($exchange->code);

        Log::info("🚀 Scraping started for exchange: {$exchange->name} ({$exchange->code})");

        try {
            $scraper->scrape();
            Log::info("✅ Scraping completed for exchange: {$exchange->name} ({$exchange->code})");
        } catch (\Throwable $e) {
            Log::error("❌ Scraping failed for {$exchange->name} ({$exchange->code})", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
