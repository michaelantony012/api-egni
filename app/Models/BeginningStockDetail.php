<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BeginningStockDetail extends Model
{
    use HasFactory;

    protected $table = 'beginning_stock_d';
    protected $guarded = ['id'];
    
    public function products(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'id_product');
    }
}
