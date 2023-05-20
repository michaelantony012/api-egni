<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Supplier extends Model
{
    use HasFactory;

    public function purchasing(): HasOne
    {
        return $this->hasOne(Purchasing::class, 'supplier_id', 'id');
    }
    public function outgoing(): HasOne
    {
        return $this->hasOne(Outgoing::class, 'supplier_id', 'id');
    }
}
