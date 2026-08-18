<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrendSnapshot extends Model
{
    protected $fillable = ['search_keyword_id', 'date', 'interest'];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function keyword(): BelongsTo
    {
        return $this->belongsTo(SearchKeyword::class, 'search_keyword_id');
    }
}
