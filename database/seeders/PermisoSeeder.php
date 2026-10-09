<?php

namespace Database\Seeders;

use App\Models\Permiso;
use Illuminate\Database\Seeder;

class PermisoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * IDs fijos requeridos para control estricto de Middleware y permisos granulares.
     */
    public function run(): void
    {
        $permisos = [
            // ==========================================
            // MÓDULO 1: ADMINISTRACIÓN Y SISTEMA (1-4, 11-21)
            // ==========================================
            1 => ['nombre' => 'gestion-usuarios', 'descripcion' => 'Acceso general al módulo de usuarios y personal'],
            11 => ['nombre' => 'usuarios.crear', 'descripcion' => 'Registrar y crear nuevas cuentas de usuarios'],
            12 => ['nombre' => 'usuarios.editar', 'descripcion' => 'Editar datos, sucursal, rol y contraseñas de usuarios'],
            13 => ['nombre' => 'usuarios.eliminar', 'descripcion' => 'Dar de baja, suspender o eliminar usuarios'],
            14 => ['nombre' => 'usuarios.permisos', 'descripcion' => 'Asignar y modificar permisos granulares de usuarios'],

            2 => ['nombre' => 'gestion-sucursales', 'descripcion' => 'Acceso general al módulo de sedes y sucursales'],
            15 => ['nombre' => 'sucursales.crear', 'descripcion' => 'Registrar nuevas sedes o sucursales'],
            16 => ['nombre' => 'sucursales.editar', 'descripcion' => 'Modificar información de sedes o sucursales'],
            17 => ['nombre' => 'sucursales.eliminar', 'descripcion' => 'Eliminar o suspender sedes o sucursales'],

            3 => ['nombre' => 'gestion-roles', 'descripcion' => 'Acceso general al módulo de roles del sistema'],
            18 => ['nombre' => 'roles.gestionar', 'descripcion' => 'Crear, modificar y eliminar roles de acceso'],

            4 => ['nombre' => 'gestion-catalogos', 'descripcion' => 'Acceso general a catálogos maestros de especialidades y servicios'],
            19 => ['nombre' => 'catalogos.gestionar', 'descripcion' => 'Crear y modificar catálogo de especialidades y servicios clínicos'],

            20 => ['nombre' => 'reportes.ver', 'descripcion' => 'Visualización de reportes, estadísticas y flujo de caja'],
            21 => ['nombre' => 'reportes.exportar', 'descripcion' => 'Exportar reportes del sistema en formatos PDF y Excel'],

            // ==========================================
            // MÓDULO 2: PACIENTES E INSTITUCIONES (5, 22-26)
            // ==========================================
            5 => ['nombre' => 'gestion-pacientes', 'descripcion' => 'Acceso general al módulo de pacientes'],
            22 => ['nombre' => 'pacientes.crear', 'descripcion' => 'Registrar nuevos pacientes en el sistema'],
            23 => ['nombre' => 'pacientes.editar', 'descripcion' => 'Modificar datos personales y clínicos de pacientes'],
            24 => ['nombre' => 'pacientes.eliminar', 'descripcion' => 'Eliminar registros de pacientes del sistema'],
            25 => ['nombre' => 'instituciones.ver', 'descripcion' => 'Consultar listado de instituciones y seguros médicos'],
            26 => ['nombre' => 'instituciones.gestionar', 'descripcion' => 'Crear, editar y administrar instituciones y convenios'],

            // ==========================================
            // MÓDULO 3: PROFORMAS CLÍNICAS Y ADMISIÓN (6-8, 27-46)
            // ==========================================
            6 => ['nombre' => 'crear-proforma', 'descripcion' => 'Apertura y registro inicial de proformas (Admisión de pacientes)'],
            7 => ['nombre' => 'gestionar-proforma', 'descripcion' => 'Acceso y visualización del listado general de proformas clínicas'],
            27 => ['nombre' => 'proformas.editar', 'descripcion' => 'Modificar datos de cabecera, diagnóstico y médicos tratantes'],
            28 => ['nombre' => 'proformas.anular', 'descripcion' => 'Anular o dar de baja proformas clínicas'],
            29 => ['nombre' => 'proformas.imprimir', 'descripcion' => 'Generar e imprimir documentos PDF de proformas y recibos'],

            // Pestaña Servicios
            30 => ['nombre' => 'proformas.servicios.ver', 'descripcion' => 'Visualizar pestaña de servicios y procedimientos clínicos'],
            31 => ['nombre' => 'proformas.servicios.agregar', 'descripcion' => 'Agregar servicios y procedimientos a la proforma'],
            32 => ['nombre' => 'proformas.servicios.eliminar', 'descripcion' => 'Eliminar servicios o procedimientos asignados'],

            // Pestaña Solicitudes / Exámenes
            33 => ['nombre' => 'proformas.solicitudes.ver', 'descripcion' => 'Visualizar pestaña de solicitudes y exámenes de laboratorio/imagen'],
            34 => ['nombre' => 'proformas.solicitudes.agregar', 'descripcion' => 'Solicitar nuevos exámenes y adjuntar archivos de resultados'],
            35 => ['nombre' => 'proformas.solicitudes.eliminar', 'descripcion' => 'Eliminar solicitudes médicas o resultados adjuntos'],

            // Pestaña Cronograma / Calendario
            36 => ['nombre' => 'proformas.cronograma.ver', 'descripcion' => 'Visualizar pestaña de cronograma, calendario y citas'],
            37 => ['nombre' => 'proformas.cronograma.agregar', 'descripcion' => 'Programar citas y eventos en el calendario clínico'],
            38 => ['nombre' => 'proformas.cronograma.gestionar', 'descripcion' => 'Actualizar estado o eliminar eventos del cronograma'],

            // Pestaña Recetas Médicas
            8 => ['nombre' => 'emitir-receta', 'descripcion' => 'Prescribir medicamentos y emitir recetas médicas'],
            39 => ['nombre' => 'proformas.recetas.ver', 'descripcion' => 'Visualizar pestaña de prescripciones y recetas médicas'],
            40 => ['nombre' => 'proformas.recetas.anular', 'descripcion' => 'Anular recetas médicas emitidas'],

            // Pestaña Consumos Extras
            41 => ['nombre' => 'proformas.consumos.ver', 'descripcion' => 'Visualizar pestaña de consumos extras e insumos'],
            42 => ['nombre' => 'proformas.consumos.agregar', 'descripcion' => 'Registrar consumos extras e insumos en la proforma'],
            43 => ['nombre' => 'proformas.consumos.eliminar', 'descripcion' => 'Eliminar consumos extras cargados a la proforma'],

            // Pestaña Honorarios Médicos
            44 => ['nombre' => 'proformas.honorarios.ver', 'descripcion' => 'Visualizar pestaña de honorarios y pagos a médicos'],
            45 => ['nombre' => 'proformas.honorarios.gestionar', 'descripcion' => 'Registrar y liquidar pagos de honorarios médicos'],
            46 => ['nombre' => 'proformas.honorarios.eliminar', 'descripcion' => 'Eliminar pagos o registros de honorarios médicos'],

            // ==========================================
            // MÓDULO 4: FARMACIA E INVENTARIO (9, 47-59)
            // ==========================================
            9 => ['nombre' => 'despachar-farmacia', 'descripcion' => 'Acceso al módulo y despacho de recetas e insumos en farmacia'],
            47 => ['nombre' => 'farmacia.productos.ver', 'descripcion' => 'Consultar catálogo de medicamentos, fármacos e insumos'],
            48 => ['nombre' => 'farmacia.productos.crear', 'descripcion' => 'Crear y registrar nuevos medicamentos e insumos'],
            49 => ['nombre' => 'farmacia.productos.editar', 'descripcion' => 'Modificar catálogo de fármacos, precios e indicaciones'],
            50 => ['nombre' => 'farmacia.productos.eliminar', 'descripcion' => 'Eliminar productos del catálogo de farmacia'],

            51 => ['nombre' => 'farmacia.lotes.ver', 'descripcion' => 'Consultar lotes, existencias y fechas de vencimiento'],
            52 => ['nombre' => 'farmacia.lotes.crear', 'descripcion' => 'Ingresar nuevos lotes y registrar abastecimiento de stock'],
            53 => ['nombre' => 'farmacia.lotes.editar', 'descripcion' => 'Modificar información de lotes, costos y vencimientos'],
            54 => ['nombre' => 'farmacia.lotes.anular', 'descripcion' => 'Dar de baja, bloquear o vencer lotes de inventario'],

            55 => ['nombre' => 'farmacia.secciones.gestionar', 'descripcion' => 'Administrar secciones, depósitos y áreas hospitalarias'],
            56 => ['nombre' => 'farmacia.despachos.revertir', 'descripcion' => 'Revertir y anular despachos de medicamentos'],
            57 => ['nombre' => 'farmacia.movimientos.ver', 'descripcion' => 'Consultar kardex y movimientos de inventario'],
            58 => ['nombre' => 'farmacia.movimientos.transferir', 'descripcion' => 'Realizar transferencias de stock entre secciones y ajustes'],
            59 => ['nombre' => 'farmacia.catalogos.gestionar', 'descripcion' => 'Administrar marcas de laboratorios y proveedores farmacéuticos'],

            // ==========================================
            // MÓDULO 5: CAJA Y FINANZAS (10, 60-70)
            // ==========================================
            10 => ['nombre' => 'cobro-caja', 'descripcion' => 'Acceso al módulo de caja y cobro de proformas clínicas'],
            60 => ['nombre' => 'caja.aperturar', 'descripcion' => 'Aperturar turnos y puntos de caja'],
            61 => ['nombre' => 'caja.cerrar', 'descripcion' => 'Realizar arqueo de caja y cierre de turno'],
            62 => ['nombre' => 'caja.cobrar', 'descripcion' => 'Registrar cobros, pagos parciales y emitir recibos'],
            63 => ['nombre' => 'caja.cobro.anular', 'descripcion' => 'Anular cobros y comprobantes de pago registrados'],
            64 => ['nombre' => 'caja.movimientos.extra', 'descripcion' => 'Registrar ingresos y egresos extraordinarios de caja'],

            65 => ['nombre' => 'caja.cierres.ver', 'descripcion' => 'Consultar historial de cierres mensuales y utilidades'],
            66 => ['nombre' => 'caja.cierres.crear', 'descripcion' => 'Aperturar nuevo periodo de cierre mensual'],
            67 => ['nombre' => 'caja.cierres.sincronizar', 'descripcion' => 'Sincronizar movimientos y ventas automáticas al cierre'],
            68 => ['nombre' => 'caja.cierres.partidas', 'descripcion' => 'Registrar partidas manuales de ingresos y egresos del cierre'],
            69 => ['nombre' => 'caja.cierres.finalizar', 'descripcion' => 'Finalizar y bloquear balance del cierre mensual'],
            70 => ['nombre' => 'caja.cierres.eliminar', 'descripcion' => 'Eliminar periodos de cierre mensual'],
        ];

        foreach ($permisos as $id => $permiso) {
            Permiso::updateOrCreate(
                ['id' => $id],
                [
                    'nombre' => $permiso['nombre'],
                    'descripcion' => $permiso['descripcion'],
                ]
            );
        }
    }
}
