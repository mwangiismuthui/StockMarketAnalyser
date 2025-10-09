<?php

namespace App\Console\Commands;

use App\Models\StockExchange;
use Illuminate\Console\Command;
use App\Jobs\ScrapeExchangeJob;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Throwable;

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
    protected $description = 'Scrape stock data for one or all exchanges using batched jobs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $exchangeCode = $this->argument('exchange');

        // Scrape one exchange if provided
        if ($exchangeCode) {
            $exchange = StockExchange::where('code', $exchangeCode)->first();

            if (!$exchange) {
                $this->error("Exchange {$exchangeCode} not found.");
                return;
            }

            Bus::batch([
                (new ScrapeExchangeJob($exchange))
                    ->onConnection('redis')
                    ->onQueue('scrapstocks')
                    ->delay(now()->addSeconds(rand(0, 10))) // random delay for safety
            ])
            ->name("Scrape {$exchange->name}")
            ->dispatch();

            $this->info("Dispatched scraping job for {$exchange->name} in a batch");
            return;
        }

        // Otherwise, scrape all exchanges using batching
        $jobs = StockExchange::all()->map(function ($exchange) {
            return (new ScrapeExchangeJob($exchange))
                ->onConnection('redis')
                ->onQueue('scrapstocks')
                ->delay(now()->addSeconds(rand(0, 120))); // stagger 0–2 min
        })->toArray();

        $batch = Bus::batch($jobs)
            ->name('Scrape All Stock Exchanges')
            ->then(function (Batch $batch) {
                Log::info('All scraping jobs completed for batch: ' . $batch->id);
            })
            ->catch(function (Batch $batch, Throwable $e) {
                Log::error('Scraping batch failed', [
                    'batch_id' => $batch->id,
                    'error' => $e->getMessage(),
                ]);
            })
            ->finally(function (Batch $batch) {
                Log::info('Scraping batch finished (either success or failure)', [
                    'batch_id' => $batch->id,
                ]);
            })
            ->dispatch();

        $this->info("Dispatched scraping batch with ID: {$batch->id} for all exchanges");
    }
}
