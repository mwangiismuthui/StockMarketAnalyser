<?php

namespace App\Services\StockScrapers;

use App\Models\Stock;
use App\Models\StockExchange;
use App\Models\StockMetric;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

abstract class BaseScraper
{
    protected StockExchange $exchange;

    public function __construct(?StockExchange $exchange = null)
    {
        $this->exchange = $exchange ?? StockExchange::where('code', $this->exchangeCode())->firstOrFail();
    }

    abstract public function scrape();
    abstract public function exchangeCode(): string;

    protected function saveStock(array $data)
    {
        try {
            // 1. Extract the exchange code from the ticker (e.g., "nase/KCB" → "NASE")
            $exchangeCode = $data['ticker'] ?? null;

            if (!$exchangeCode) {
                Log::warning('Missing ticker', ['data' => $data]);
                return;
            }

            $exchangeCode = strtoupper(trim(explode('/', $exchangeCode)[0] ?? ''));

            if (empty($exchangeCode)) {
                Log::warning('Missing exchange code after parsing ticker', ['ticker' => $data['ticker']]);
                return;
            }

            // 2. Get the Stock Exchange from the DB using the code
            $exchange_id = StockExchange::where('code', $exchangeCode)->pluck('id')->first();
            Log::info('Resolved exchange ID', ['code' => $exchangeCode, 'id' => $exchange_id]);

            if (!$exchange_id) {
                Log::warning('Stock exchange not found for code', ['code' => $exchangeCode]);
                return;
            }


            $stock = Stock::updateOrCreate(
                ['ticker' => $data['ticker']],
                [
                    'stock_exchange_id' => $exchange_id,
                    'name' => $data['n'] ?? $data['name'] ?? 'Unknown',
                    'industry' => $data['industry'] ?? null,
                    'employees' => $data['employees'] ?? null,
                    'founded' => $data['founded'] ?? null,
                ]
            );

            // 3. Create a new StockMetric entry (always insert — don't update)
            StockMetric::updateOrCreate(
                [
                    // 👉 Unique fields to match an existing record
                    'stock_id' => $stock->id,
                    'recorded_at' => Carbon::parse($data['timestamp'] ?? now()),
                ],
                [
                    // 👉 Fields to update if record exists
                    'price' => $data['price'] ?? null,
                    'change' => $data['change'] ?? null,
                    'market_cap' => $data['marketCap'] ?? null,
                    'revenue' => $data['revenue'] ?? null,

                    'tr1m' => $data['tr1m'] ?? null,
                    'tr6m' => $data['tr6m'] ?? null,
                    'trYTD' => $data['trYTD'] ?? null,
                    'tr1y' => $data['tr1y'] ?? null,
                    'tr5y' => $data['tr5y'] ?? null,
                    'tr10y' => $data['tr10y'] ?? null,

                    'dps' => $data['dps'] ?? null,
                    'dividend_yield' => $data['dividendYield'] ?? null,
                    'dividend_growth' => $data['dividendGrowth'] ?? null,
                    'ex_div_date' => $data['exDivDate'] ?? null,
                    'payout_ratio' => $data['payoutRatio'] ?? null,
                    'payout_frequency' => $data['payoutFrequency'] ?? null,

                    'volume' => $data['volume'] ?? null,
                    'low_52' => $data['low52'] ?? null,
                    'low_52_ch' => $data['low52ch'] ?? null,
                    'high_52' => $data['high52'] ?? null,
                    'high_52_ch' => $data['high52ch'] ?? null,
                ]
            );

            Log::info("Stock data saved successfully for {$stock->ticker}");
        } catch (\Exception $e) {
            Log::error('Error saving stock data', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
        }
    }
}
