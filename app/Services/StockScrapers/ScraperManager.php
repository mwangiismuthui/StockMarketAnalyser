<?php

namespace App\Services\StockScrapers;

use App\Models\StockExchange;
use Illuminate\Support\Facades\Log;

class ScraperManager
{
    public function scrape(StockExchange $exchange)
    {
        $scraper = $this->resolveScraper($exchange->code);

        if ($scraper) {
            Log::info("Using scraper for exchange: {$exchange->name} ({$exchange->code})");
            $scraper->scrape();
        }
    }

    protected function resolveScraper(string $exchangeCode): ?BaseScraper
    {
        return match (strtolower($exchangeCode)) {
            'nase' => new NseScraper(),
            default => null,
        };
    }
}
