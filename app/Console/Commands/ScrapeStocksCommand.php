<?php

namespace App\Console\Commands;

use App\Models\StockExchange;
use Illuminate\Console\Command;
use App\Jobs\ScrapeExchangeJob;
use App\Services\StockScrapers\ScraperManager;

class ScrapeStocksCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stocks:scrape {exchange?}';

    /**
     * The console command description.
     *
     * @var string
     */
protected $description = 'Scrape stock data for one or all exchanges';
    /**
     * Execute the console command.
     */
   public function handle()
    {
        $exchangeCode = $this->argument('exchange');

        if ($exchangeCode) {
            $exchange = StockExchange::where('code', $exchangeCode)->first();
            if ($exchange) {
                ScrapeExchangeJob::dispatch($exchange)
                    ->onConnection('redis')
                    ->onQueue('scrapstocks');

                $this->info("Dispatched scraping job for {$exchange->name} on 'scrapstocks' queue");
            } else {
                $this->error("Exchange {$exchangeCode} not found.");
            }
        } else {
            StockExchange::all()->each(function ($exchange) {
                ScrapeExchangeJob::dispatch($exchange)
                    ->onConnection('redis')
                    ->onQueue('scrapstocks');

                $this->info("Dispatched scraping job for {$exchange->name} on 'scrapstocks' queue");
            });
        }
    }
}
