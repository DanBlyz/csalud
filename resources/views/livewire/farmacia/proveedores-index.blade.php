<div class="space-y-6">
    <!-- Breadcrumb y Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-teal-600 transition">Inicio</a>
                <i class="fas fa-chevron-right text-[10px]"></i>
                <span class="text-slate-600 dark:text-slate-400">Farmacia e Inventario</span>
                <i class="fas fa-chevron-right text-[10px]"></i>
                <span class="text-slate-800 dark:text-slate-200">Proveedores y Droguerías</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-teal-600 text-white shadow-md shadow-teal-500/20">
                    <i class="fas fa-truck text-lg"></i>
                </span>
                Proveedores y Droguerías
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Registro y trazabilidad de laboratorios, distribuidoras y droguerías proveedoras de stock médico hospitalario.
            </p>
        </div>

        @permiso('farmacia.catalogos.gestionar')
        <button 
            type="button" 
            wire:click="abrirModal" 
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-md shadow-teal-600/20 hover:shadow-lg transition-all transform active:scale-95 cursor-pointer"
        >
            <i class="fas fa-plus text-xs"></i>
            <span>Nuevo Proveedor / Droguería</span>
        </button>
        @endpermiso
    </div>

    <!-- Métricas Rápidas -->
    {{-- <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Droguerías / Proveedores</span>
                <div class="text-2xl font-black font-mono text-slate-900 dark:text-white mt-0.5">
                    {{ number_format($totalProveedores) }}
                </div>
                <span class="text-[11px] text-slate-500 dark:text-slate-400">Empresas suministradoras registradas</span>
            </div>
            <div class="p-3 bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400 rounded-xl">
                <i class="fas fa-building text-xl"></i>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Lotes y Compras Abastecidas</span>
                <div class="text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400 mt-0.5">
                    {{ number_format($totalLotesAbastecidos) }}
                </div>
                <span class="text-[11px] text-slate-500 dark:text-slate-400">Entregas de inventario registradas</span>
            </div>
            <div class="p-3 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 rounded-xl">
                <i class="fas fa-boxes text-xl"></i>
            </div>
        </div>
    </div> --}}

    <!-- Main Card Container -->
    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs overflow-hidden transition-colors duration-200">
        <!-- Card Header: Controles Estándar -->
        <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2">
                        <label for="perPageProv" class="text-xs font-medium text-slate-600 dark:text-slate-400">Mostrar:</label>
                        <select 
                            id="perPageProv" 
                            wire:model.live="perPage" 
                            class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 py-1.5 px-2.5 shadow-2xs"
                        >
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="text-xs text-slate-500 dark:text-slate-400">registros</span>
                    </div>
                </div>

                <!-- Buscador Debounce -->
                <div class="relative w-full md:w-80">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </div>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Buscar por empresa, NIT, contacto o celular..." 
                        class="w-full pl-9 pr-8 py-2 text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-teal-500 shadow-2xs"
                    />
                    @if ($search !== '')
                        <button wire:click="$set('search', '')" type="button" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <i class="fas fa-times-circle text-xs"></i>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tabla de Proveedores -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50/80 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[11px] font-bold border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-5 py-3.5">Razón Social / NIT</th>
                        <th class="px-5 py-3.5">Persona de Contacto</th>
                        <th class="px-5 py-3.5">Teléfonos / Correo</th>
                        <th class="px-5 py-3.5">Dirección</th>
                        <th class="px-5 py-3.5 text-center">Lotes Provistos</th>
                        <th class="px-5 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($proveedores as $proveedor)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400 border border-teal-200 dark:border-teal-800 flex items-center justify-center font-bold text-xs shrink-0">
                                        {{ strtoupper(substr($proveedor->razon_social, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 dark:text-white text-sm">
                                            {{ $proveedor->razon_social }}
                                        </div>
                                        <div class="text-[11px] text-slate-400 font-mono mt-0.5">
                                            NIT/RUC: {{ $proveedor->nit_ruc ?: 'Sin NIT registrado' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-medium text-slate-800 dark:text-slate-200">
                                    {{ $proveedor->contacto_nombre ?: 'No especificado' }}
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="space-y-0.5">
                                    @if($proveedor->celular || $proveedor->telefono)
                                        <div class="flex items-center gap-1.5 text-slate-700 dark:text-slate-300">
                                            <i class="fas fa-phone-alt text-[10px] text-teal-500"></i>
                                            <span>{{ $proveedor->celular ?: $proveedor->telefono }}</span>
                                        </div>
                                    @endif
                                    @if($proveedor->correo)
                                        <div class="flex items-center gap-1.5 text-slate-500 dark:text-slate-400 text-[11px]">
                                            <i class="fas fa-envelope text-[10px] text-slate-400"></i>
                                            <span class="truncate max-w-[180px]">{{ $proveedor->correo }}</span>
                                        </div>
                                    @endif
                                    @if(! $proveedor->celular && ! $proveedor->telefono && ! $proveedor->correo)
                                        <span class="text-slate-400 italic text-[11px]">Sin datos de contacto</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-slate-600 dark:text-slate-400 text-xs line-clamp-2">
                                    {{ $proveedor->direccion ?: 'Sin dirección registrada.' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <button 
                                    type="button" 
                                    wire:click="verLotes({{ $proveedor->id }})"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold {{ $proveedor->lotes_count > 0 ? 'bg-teal-50 hover:bg-teal-100 text-teal-700 dark:bg-teal-950/60 dark:text-teal-300 border border-teal-200 dark:border-teal-800' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }} transition cursor-pointer"
                                    title="Ver Lotes Suministrados"
                                >
                                    <i class="fas fa-boxes text-[10px]"></i>
                                    {{ $proveedor->lotes_count }} Lotes
                                </button>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    @permiso('farmacia.catalogos.gestionar')
                                    <button 
                                        type="button" 
                                        wire:click="abrirModal({{ $proveedor->id }})" 
                                        class="p-2 text-teal-600 hover:text-teal-800 dark:text-teal-400 dark:hover:text-teal-300 hover:bg-teal-50 dark:hover:bg-slate-800 rounded-xl transition cursor-pointer"
                                        title="Editar Proveedor"
                                    >
                                        <i class="fas fa-pen text-xs"></i>
                                    </button>

                                    <button 
                                        type="button" 
                                        @click="$dispatch('swal:confirm', {
                                            title: '¿Eliminar Proveedor?',
                                            text: 'Se eliminará a {{ $proveedor->razon_social }} del directorio de proveedores.',
                                            icon: 'warning',
                                            confirmButtonText: 'Sí, eliminar',
                                            cancelButtonText: 'Cancelar',
                                            event: 'eliminarProveedor',
                                            componentId: '{{ $this->getId() }}',
                                            method: 'eliminar',
                                            params: [{{ $proveedor->id }}]
                                        })"
                                        class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl transition cursor-pointer"
                                        title="Eliminar Proveedor"
                                    >
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                    @endpermiso
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                                <i class="fas fa-truck text-3xl mb-2 text-slate-300 dark:text-slate-600 block"></i>
                                No se encontraron proveedores o droguerías registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación Única Estándar -->
        @if ($proveedores->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                {{ $proveedores->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL CREAR / EDITAR PROVEEDOR -->
    @if($modalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity" wire:click="cerrarModal"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-xl">
                    <form wire:submit="guardar">
                        <!-- Modal Header -->
                        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal-600 text-white text-sm">
                                    <i class="fas {{ $proveedorId ? 'fa-edit' : 'fa-plus' }}"></i>
                                </span>
                                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100" id="modal-title">
                                    {{ $proveedorId ? 'Editar Proveedor' : 'Nuevo Proveedor' }}
                                </h3>
                            </div>
                            <button 
                                wire:click="cerrarModal" 
                                type="button" 
                                class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg hover:bg-slate-200/60 dark:hover:bg-slate-700 transition-colors cursor-pointer"
                            >
                                <i class="fas fa-times text-base"></i>
                            </button>
                        </div>

                        <!-- Body Modal -->
                        <div class="p-6 space-y-4">
                            <!-- Razón Social y NIT -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                        Razón Social / Empresa <span class="text-rose-500">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        wire:model="razon_social" 
                                        placeholder="Ej. Droguería INTI S.A., Farmacorp..."
                                        class="w-full text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 py-2 px-3 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500"
                                    >
                                    @error('razon_social')
                                        <span class="text-[11px] text-rose-500 block mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                        NIT / RUC
                                    </label>
                                    <input 
                                        type="text" 
                                        wire:model="nit_ruc" 
                                        placeholder="Ej. 1020304050"
                                        class="w-full text-xs font-mono rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 py-2 px-3 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500"
                                    >
                                    @error('nit_ruc')
                                        <span class="text-[11px] text-rose-500 block mt-1">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Contacto y Teléfonos -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                        Persona de Contacto
                                    </label>
                                    <input 
                                        type="text" 
                                        wire:model="contacto_nombre" 
                                        placeholder="Ej. Lic. Roberto Gómez"
                                        class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 py-2 px-3 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500"
                                    >
                                </div>

                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                        Celular
                                    </label>
                                    <input 
                                        type="text" 
                                        wire:model="celular" 
                                        placeholder="Ej. 70012345"
                                        class="w-full text-xs font-mono rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 py-2 px-3 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500"
                                    >
                                </div>

                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                        Teléfono Fijo
                                    </label>
                                    <input 
                                        type="text" 
                                        wire:model="telefono" 
                                        placeholder="Ej. 2-2445566"
                                        class="w-full text-xs font-mono rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 py-2 px-3 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500"
                                    >
                                </div>
                            </div>

                            <!-- Correo y Dirección -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                        Correo Electrónico
                                    </label>
                                    <input 
                                        type="email" 
                                        wire:model="correo" 
                                        placeholder="ventas@distribuidora.com"
                                        class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 py-2 px-3 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500"
                                    >
                                    @error('correo')
                                        <span class="text-[11px] text-rose-500 block mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                        Dirección / Ubicación
                                    </label>
                                    <input 
                                        type="text" 
                                        wire:model="direccion" 
                                        placeholder="Ej. Av. América #456, Zona Central"
                                        class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 py-2 px-3 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500"
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Footer Modal -->
                        <div class="bg-slate-50 dark:bg-slate-800/60 px-6 py-4 border-t border-slate-200 dark:border-slate-700 flex items-center justify-end gap-3">
                            <button 
                                type="button" 
                                wire:click="cerrarModal" 
                                class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition cursor-pointer"
                            >
                                Cancelar
                            </button>

                            <button 
                                type="submit" 
                                wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-md shadow-teal-600/20 hover:shadow-lg transition transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                            >
                                <span wire:loading.remove wire:target="guardar">
                                    <i class="fas fa-save text-xs"></i>
                                    {{ $proveedorId ? 'Actualizar Proveedor' : 'Guardar Proveedor' }}
                                </span>
                                <span wire:loading wire:target="guardar" class="inline-flex items-center gap-2">
                                    <i class="fas fa-circle-notch fa-spin text-xs"></i>
                                    Guardando...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL VER LOTES / ENTREGAS DEL PROVEEDOR -->
    @if($modalLotesOpen && $proveedorDetalle)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity" wire:click="cerrarModalLotes"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-3xl">
                    <!-- Modal Header -->
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal-600 text-white text-sm">
                                <i class="fas fa-boxes"></i>
                            </span>
                            <div>
                                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100" id="modal-title">
                                    Lotes Provistos: {{ $proveedorDetalle->razon_social }}
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    NIT: {{ $proveedorDetalle->nit_ruc ?: 'S/N' }} &bull; Total Lotes: {{ $proveedorDetalle->lotes->count() }}
                                </p>
                            </div>
                        </div>
                        <button 
                            wire:click="cerrarModalLotes" 
                            type="button" 
                            class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg hover:bg-slate-200/60 dark:hover:bg-slate-700 transition-colors cursor-pointer"
                        >
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>

                    <!-- Body Modal -->
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[11px] font-bold border-b border-slate-200 dark:border-slate-700">
                                    <tr>
                                        <th class="px-4 py-2.5">Lote</th>
                                        <th class="px-4 py-2.5">Medicamento / Insumo</th>
                                        <th class="px-4 py-2.5">Sucursal</th>
                                        <th class="px-4 py-2.5 text-center">Stock Actual</th>
                                        <th class="px-4 py-2.5">Vencimiento</th>
                                        <th class="px-4 py-2.5 text-right">P. Compra</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @forelse($proveedorDetalle->lotes as $lote)
                                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                                            <td class="px-4 py-3 font-mono font-bold text-slate-800 dark:text-slate-200">
                                                {{ $lote->codigo_lote }}
                                            </td>
                                            <td class="px-4 py-3 font-medium text-slate-900 dark:text-white">
                                                {{ $lote->producto->nombre ?? 'S/N' }}
                                            </td>
                                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                                {{ $lote->sucursal->nombre ?? 'General' }}
                                            </td>
                                            <td class="px-4 py-3 text-center font-bold">
                                                {{ $lote->cantidad_actual }} / {{ $lote->cantidad_ingresada }}
                                            </td>
                                            <td class="px-4 py-3 font-mono text-[11px]">
                                                {{ $lote->fecha_vencimiento ? $lote->fecha_vencimiento->format('d/m/Y') : 'S/F' }}
                                            </td>
                                            <td class="px-4 py-3 text-right font-mono text-slate-700 dark:text-slate-300">
                                                Bs. {{ number_format($lote->precio_compra, 2) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-4 py-8 text-center text-slate-400">
                                                Este proveedor aún no tiene lotes suministrados en el inventario.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Footer Modal -->
                    <div class="bg-slate-50 dark:bg-slate-800/60 px-6 py-4 border-t border-slate-200 dark:border-slate-700 flex justify-end">
                        <button 
                            type="button" 
                            wire:click="cerrarModalLotes" 
                            class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition cursor-pointer"
                        >
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
