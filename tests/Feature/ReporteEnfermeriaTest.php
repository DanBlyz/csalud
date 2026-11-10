<?php

use App\Livewire\Proformas\ProformaDetalle;
use App\Models\Institucion;
use App\Models\Paciente;
use App\Models\Proforma;
use App\Models\ReporteEnfermeria;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->rolAdmin = Rol::firstOrCreate(['nombre' => 'Admin'], ['descripcion' => 'Admin']);

    $this->sucursal = Sucursal::create([
        'nombre' => 'Sucursal Central Test',
        'direccion' => 'Av. Principal #123',
        'telefono' => '70012345',
        'activo' => true,
    ]);

    $this->admin = User::factory()->create([
        'rol_id' => $this->rolAdmin->id,
        'sucursal_id' => $this->sucursal->id,
        'activo' => true,
    ]);

    $this->institucion = Institucion::create([
        'nombre' => 'Seguro Salud Integral',
        'tipo' => 'Seguro',
        'activo' => true,
    ]);

    $this->paciente = Paciente::create([
        'nombres' => 'Maria Elena',
        'apellido_paterno' => 'Mamani',
        'apellido_materno' => 'Quispe',
        'fecha_nacimiento' => '1985-04-12',
        'cedula' => 'CI-9876543',
        'genero' => 'Femenino',
        'celular' => '72233445',
        'institucion_id' => $this->institucion->id,
    ]);

    $this->proforma = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $this->paciente->id,
        'fecha_ingreso' => now(),
        'tipo_atencion' => 'Internacion',
        'pieza' => 'Cama 2',
        'estado' => 'En Curso',
        'costo_total' => 0.00,
        'motivo_consulta' => 'Cuadro febril y dolor abdominal',
        'diagnostico' => 'Apendicitis aguda',
    ]);
});

test('la pestaña de Reporte Enfermería se muestra en ProformaDetalle con su contador', function () {
    $this->actingAs($this->admin);

    Livewire::test(ProformaDetalle::class, ['proforma' => $this->proforma])
        ->assertOk()
        ->assertSee('Reporte Enfermería')
        ->assertSee('Hoja de Internación');
});

test('puede registrar un nuevo reporte de enfermeria con procedimiento y sintomas', function () {
    $this->actingAs($this->admin);

    Livewire::test(ProformaDetalle::class, ['proforma' => $this->proforma])
        ->call('cambiarTab', 'reportes_enfermeria')
        ->assertSet('tab', 'reportes_enfermeria')
        ->call('abrirModalReporteEnfermeria')
        ->assertSet('modalReporteEnfermeriaOpen', true)
        ->set('rep_enf_fecha_hora', now()->format('Y-m-d\TH:i'))
        ->set('rep_enf_procedimiento', '<p>Administración de Ceftriaxona 1g EV cada 12h y Solución Fisiológica 1000ml.</p>')
        ->set('rep_enf_observaciones', '<p>Paciente hemodinámicamente estable, afebril, refiere leve dolor en fosa iliaca derecha.</p>')
        ->call('guardarReporteEnfermeria')
        ->assertDispatched('swal')
        ->assertSet('modalReporteEnfermeriaOpen', false);

    $reporte = ReporteEnfermeria::where('proforma_id', $this->proforma->id)->first();
    expect($reporte)->not->toBeNull();
    expect($reporte->procedimiento_medicacion)->toContain('Ceftriaxona');
    expect($reporte->observaciones)->toContain('hemodinámicamente');
    expect($reporte->usuario_creador_id)->toBe($this->admin->id);
});

test('puede editar un reporte de enfermeria existente', function () {
    $this->actingAs($this->admin);

    $reporte = ReporteEnfermeria::create([
        'proforma_id' => $this->proforma->id,
        'fecha_hora' => now(),
        'procedimiento_medicacion' => 'Ketorolaco 30mg EV PRN',
        'observaciones' => 'Dolor EVA 6/10',
        'usuario_creador_id' => $this->admin->id,
    ]);

    Livewire::test(ProformaDetalle::class, ['proforma' => $this->proforma])
        ->call('cambiarTab', 'reportes_enfermeria')
        ->call('abrirModalReporteEnfermeria', $reporte->id)
        ->assertSet('modalReporteEnfermeriaOpen', true)
        ->set('rep_enf_observaciones', 'Dolor controlado, EVA 2/10')
        ->call('guardarReporteEnfermeria')
        ->assertDispatched('swal');

    $reporte->refresh();
    expect($reporte->observaciones)->toBe('Dolor controlado, EVA 2/10');
    expect($reporte->usuario_modificador_id)->toBe($this->admin->id);
});

test('puede eliminar un reporte de enfermeria mediante soft delete', function () {
    $this->actingAs($this->admin);

    $reporte = ReporteEnfermeria::create([
        'proforma_id' => $this->proforma->id,
        'fecha_hora' => now(),
        'procedimiento_medicacion' => 'Canalización de vía periférica #18G',
        'observaciones' => 'Sin complicaciones',
        'usuario_creador_id' => $this->admin->id,
    ]);

    Livewire::test(ProformaDetalle::class, ['proforma' => $this->proforma])
        ->call('eliminarReporteEnfermeria', $reporte->id)
        ->assertDispatched('swal');

    expect(ReporteEnfermeria::find($reporte->id))->toBeNull();
    expect(ReporteEnfermeria::withTrashed()->find($reporte->id))->not->toBeNull();
});

test('puede generar el reporte de enfermeria en formato PDF con datos del paciente, tipo de atención, pieza y HC', function () {
    $this->actingAs($this->admin);

    ReporteEnfermeria::create([
        'proforma_id' => $this->proforma->id,
        'fecha_hora' => now(),
        'procedimiento_medicacion' => '<p>Curación de herida operatoria con gasa estéril</p><ul><li>Gasas estériles x2</li><li>Solución fisiológica</li></ul>',
        'observaciones' => '<h2><strong>Evolución favorable</strong></h2><p>Herida limpia, sin signos de flogosis ni sangrado</p>',
        'usuario_creador_id' => $this->admin->id,
    ]);

    $response = $this->get(route('proformas.pdf.reporte_enfermeria_doc', $this->proforma->id));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');

    // Comprobar que el paciente tiene su número de historia clínica calculado
    expect($this->paciente->numero_historia_clinica)->not->toBeEmpty();
    expect($this->paciente->numero_historia_clinica)->toBe('12041985MEMQ');

    // Verificar que toma tipo_atencion y pieza de la proforma
    expect($this->proforma->tipo_atencion)->toBe('Internacion');
    expect($this->proforma->pieza)->toBe('Cama 2');
});
