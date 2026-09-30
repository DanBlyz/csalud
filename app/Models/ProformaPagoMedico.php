<?php

namespace App\Models;

use App\Traits\Auditable;
use Database\Factories\ProformaPagoMedicoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProformaPagoMedico extends Model
{
    /** @use HasFactory<ProformaPagoMedicoFactory> */
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'proforma_pago_medicos';

    protected $fillable = [
        'proforma_id',
        'medico_id',
        'monto',
        'observaciones',
        'fecha_pago',
        'user_id',
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'fecha_pago' => 'date',
        ];
    }

    public function proforma(): BelongsTo
    {
        return $this->belongsTo(Proforma::class, 'proforma_id');
    }

    public function medico(): BelongsTo
    {
        return $this->belongsTo(User::class, 'medico_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
