<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
      protected $fillable = [
        'stock_exchange_id', 'ticker', 'name', 'industry', 'employees', 'founded'
    ];

    public function exchange()
    {
        return $this->belongsTo(StockExchange::class, 'stock_exchange_id');
    }

    public function metrics()
    {
        return $this->hasMany(StockMetric::class);
    }
}
