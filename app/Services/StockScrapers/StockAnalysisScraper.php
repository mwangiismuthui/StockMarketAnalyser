<?php

namespace App\Services\StockScrapers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StockAnalysisScraper extends BaseScraper
{
    protected string $exchangeCodeUpperCase;
    protected string $exchangeCodeLowerCase;
    protected string $baseUrl = 'https://stockanalysis.com/api/screener/a/f';


     public function __construct(string $exchangeCode)
    {
        $this->exchangeCodeUpperCase = strtoupper($exchangeCode);
        $this->exchangeCodeLowerCase = strtolower($exchangeCode);
    }
    public function exchangeCode(): string
    {
        return $this->exchangeCodeLowerCase;
    }
    public function scrape()
    {
        // ✅ Define reusable endpoint templates
        $endpoints = [
            'overview' => [
                'm' => 'marketCap',
                's' => 'desc',
                'c' => 'no,s,n,marketCap,price,change,revenue',
                'sc' => 'marketCap',
                'f' => '', // filled dynamically below
                'i' => 'symbols',
            ],
            'performance' => [
                'm' => 'marketCap',
                's' => 'desc',
                'c' => 'no,s,tr1m,tr6m,trYTD,tr1y,tr5y,tr10y,marketCap',
                'sc' => 'marketCap',
                'f' => '',
                'i' => 'symbols',
            ],
            'dividends' => [
                'm' => 'marketCap',
                's' => 'desc',
                'c' => 'no,s,dps,dividendYield,dividendGrowth,exDivDate,payoutRatio,payoutFrequency,marketCap',
                'sc' => 'marketCap',
                'f' => '',
                'i' => 'symbols',
            ],
            'technicals' => [
                'm' => 'marketCap',
                's' => 'desc',
                'c' => 'no,s,price,change,volume,low52,low52ch,high52,high52ch,marketCap',
                'sc' => 'marketCap',
                'f' => '',
                'i' => 'symbols',
            ],
            'profile' => [
                'm' => 'marketCap',
                's' => 'desc',
                'c' => 'no,s,n,industry,country,employees,founded,marketCap',
                'sc' => 'marketCap',
                'f' => '',
                'i' => 'symbols',
            ],
        ];

        // ✅ Storage for merged data
        $mergedData = [];

        // ✅ Loop through each endpoint
        foreach ($endpoints as $key => $params) {
            // Inject the current exchange code dynamically
            $params['f'] = "exchangeCode-is-{$this->exchangeCodeUpperCase},subtype-is-stock";

            $page = 1;
            $hasMore = true;

            while ($hasMore) {
                $params['p'] = $page; // pagination param

                $response = Http::timeout(30)->get($this->baseUrl, $params);

                if ($response->successful()) {
                    $data = $response->json('data.data') ?? [];

                    // If no data on this page, stop pagination for this endpoint
                    if (empty($data)) {
                        $hasMore = false;
                        break;
                    }

                    foreach ($data as $stock) {
                        $ticker = $stock['s'] ?? null;
                        if (!$ticker)
                            continue;

                        // Ensure ticker entry exists
                        if (!isset($mergedData[$ticker])) {
                            $mergedData[$ticker] = [
                                'ticker' => $ticker,
                                'exchange_code' => $this->exchangeCodeLowerCase,
                                'timestamp' => Carbon::now()->toISOString(),
                            ];
                        }

                        // Merge the fields from this endpoint
                        $mergedData[$ticker] = array_merge($mergedData[$ticker], $stock);
                    }

                    // If the number of records < 500 (page size), stop
                    $hasMore = count($data) >= 500;
                    $page++;
                } else {
                    Log::error("Failed to fetch {$key} data", [
                        'url' => $this->baseUrl,
                        'params' => $params,
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                    $hasMore = false;
                }
            }
        }

        // ✅ Convert to indexed array
        $finalStocks = array_values($mergedData);

        // ✅ Save to DB
        foreach ($finalStocks as $stockData) {
            $this->saveStock($stockData);
        }

        Log::info("✅ Scraping completed for {$this->exchangeCodeLowerCase}", [
            'stocks_saved' => count($finalStocks),
        ]);
    }
}
