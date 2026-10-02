<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoteSeccion extends Model
{
    use HasFactory;

    protected $table = 'lote_secciones';

    protected $fillable = [
        'lote_id',
        'seccion_id',
        'cantidad_actual',
    ];

    protected function casts(): array
    {
        return [
            'cantidad_actual' => 'integer',
        ];
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }

    public function seccion(): BelongsTo
    {
        return $this->belongsTo(Seccion::class, 'seccion_id');
    }
}
