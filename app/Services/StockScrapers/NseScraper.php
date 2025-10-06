<?php

namespace App\Services\StockScrapers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NseScraper extends BaseScraper
{
    protected function exchangeCode(): string
    {
        return 'nase';
    }

  public function scrape()
{
    $baseUrl = 'https://stockanalysis.com/api/screener/a/f';

    // ✅ Define the different endpoints and their query params
    $endpoints = [
        'overview' => [
            'm' => 'marketCap',
            's' => 'desc',
            'c' => 'no,s,n,marketCap,price,change,revenue',
            'sc' => 'marketCap',
            'f' => 'exchangeCode-is-NASE,subtype-is-stock',
            'i' => 'symbols',
        ],
        'performance' => [
            'm' => 'marketCap',
            's' => 'desc',
            'c' => 'no,s,tr1m,tr6m,trYTD,tr1y,tr5y,tr10y,marketCap',
            'sc' => 'marketCap',
            'f' => 'exchangeCode-is-NASE,subtype-is-stock',
            'i' => 'symbols',
        ],
        'dividends' => [
            'm' => 'marketCap',
            's' => 'desc',
            'c' => 'no,s,dps,dividendYield,dividendGrowth,exDivDate,payoutRatio,payoutFrequency,marketCap',
            'sc' => 'marketCap',
            'f' => 'exchangeCode-is-NASE,subtype-is-stock',
            'i' => 'symbols',
        ],
        'technicals' => [
            'm' => 'marketCap',
            's' => 'desc',
            'c' => 'no,s,price,change,volume,low52,low52ch,high52,high52ch,marketCap',
            'sc' => 'marketCap',
            'f' => 'exchangeCode-is-NASE,subtype-is-stock',
            'i' => 'symbols',
        ],
        'profile' => [
            'm' => 'marketCap',
            's' => 'desc',
            'c' => 'no,s,n,industry,country,employees,founded,marketCap',
            'sc' => 'marketCap',
            'f' => 'exchangeCode-is-NASE,subtype-is-stock',
            'i' => 'symbols',
        ],
    ];

    // ✅ Storage for merged data
    $mergedData = [];

    // ✅ Fetch and merge each endpoint’s data
    foreach ($endpoints as $key => $params) {
        $response = Http::timeout(30)->get($baseUrl, $params);

        if ($response->successful()) {
            $data = $response->json('data.data') ?? [];

            foreach ($data as $stock) {
                $ticker = $stock['s'] ?? null;
                if (!$ticker) continue;

                // Ensure ticker entry exists
                if (!isset($mergedData[$ticker])) {
                    $mergedData[$ticker] = [
                        'ticker' =>  $ticker, // You can adjust this per exchange
                        'timestamp' => Carbon::now()->toISOString(),
                    ];
                }

                // Merge the fields from this endpoint
                $mergedData[$ticker] = array_merge($mergedData[$ticker], $stock);
            }
        } else {
            Log::error("Failed to fetch {$key} data", [
                'url' => $baseUrl,
                'params' => $params,
                'status' => $response->status(),
                'body' => $response->body()
            ]);
        }
    }

    // ✅ Convert to indexed array for easier JSON export
    $finalStocks = array_values($mergedData);


    // Log::info('Merged Stocks Data Count', ['count' => count($finalStocks)]);
    // Log::info('Merged Stocks Data', ['data' => $finalStocks]);
    // Example: store each stock
    foreach ($finalStocks as $stockData) {
        $this->saveStock($stockData);
    }

}
}
