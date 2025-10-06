<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMetric extends Model
{
     protected $fillable = [
        'stock_id', 'recorded_at', 'price', 'change', 'market_cap', 'revenue',
        'tr1m', 'tr6m', 'trYTD', 'tr1y', 'tr5y', 'tr10y',
        'dps', 'dividend_yield', 'dividend_growth', 'ex_div_date', 'payout_ratio', 'payout_frequency',
        'volume', 'low_52', 'low_52_ch', 'high_52', 'high_52_ch'
    ];

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }
}
