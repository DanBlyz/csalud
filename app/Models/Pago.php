<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pago extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'pagos';

    protected $fillable = [
        'caja_id',
        'proforma_id',
        'tipo_movimiento',
        'categoria',
        'tipo_pago',
        'concepto',
        'monto',
        'numero_referencia',
        'user_id',
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
        ];
    }

    public function caja(): BelongsTo
    {
        return $this->belongsTo(Caja::class, 'caja_id');
    }

    public function proforma(): BelongsTo
    {
        return $this->belongsTo(Proforma::class, 'proforma_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isIngreso(): bool
    {
        return in_array($this->tipo_movimiento, ['Ingreso Proforma', 'Ingreso Extra'], true);
    }

    public function isEgreso(): bool
    {
        return in_array($this->tipo_movimiento, ['Egreso Caja', 'Egreso'], true);
    }
}
