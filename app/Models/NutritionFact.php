<?php

namespace App\Models;

use App\Enums\Nutrient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NutritionFact extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['nutrient' => Nutrient::class, 'amount' => 'float', 'daily_value_percent' => 'float'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
