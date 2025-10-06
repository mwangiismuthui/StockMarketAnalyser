<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockExchange extends Model
{
   protected $fillable = [
        'code',
        'name',
        'country',
        'currency',
        'stocks',
    ];

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }
}
