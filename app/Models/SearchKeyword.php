<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SearchKeyword extends Model
{
    protected $fillable = ['term', 'language', 'geo', 'category', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(TrendSnapshot::class);
    }

    /**
     * % de crecimiento entre el promedio de los últimos 7 días y los 7 anteriores.
     * Es la señal simple de "esto está despegando".
     */
    public function growthScore(): float
    {
        $recent = $this->snapshots()->latest('date')->take(7)->avg('interest') ?? 0;
        $previous = $this->snapshots()->latest('date')->skip(7)->take(7)->avg('interest') ?? 0;

        if ($previous <= 0) {
            return $recent > 0 ? 100.0 : 0.0;
        }

        return round((($recent - $previous) / $previous) * 100, 2);
    }
}
