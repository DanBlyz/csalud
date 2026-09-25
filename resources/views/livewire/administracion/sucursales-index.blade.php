<div>
    <!-- Breadcrumb & Header Title -->
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-800 dark:text-slate-100 flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-600 text-white shadow-md shadow-blue-500/20">
                    <i class="fas fa-hospital text-base"></i>
                </span>
                Sedes y Sucursales
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Administración de centros operativos, clínicas y sedes de atención.
            </p>
        </div>

        <button 
            wire:click="abrirModalCrear" 
            wire:loading.attr="disabled"
            type="button" 
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-gradient-to-r from-blue-600 to-teal-600 hover:from-blue-700 hover:to-teal-700 text-white text-xs sm:text-sm font-semibold shadow-md shadow-blue-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
        >
            <i class="fas fa-plus"></i>
            <span>Nueva Sucursal</span>
        </button>
    </div>

    <!-- Main Card Container (AdminLTE Style) -->
    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs overflow-hidden transition-colors duration-200">
        <!-- Card Header: Controles Estándar de Listado -->
        <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <!-- Izquierda: Selector de Paginación y Filtro de Estado -->
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2">
                        <label for="perPage" class="text-xs font-medium text-slate-600 dark:text-slate-400">Mostrar:</label>
                        <select 
                            id="perPage" 
                            wire:model.live="perPage" 
                            class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 py-1.5 px-2.5 shadow-2xs"
                        >
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="text-xs text-slate-500 dark:text-slate-400">registros</span>
                    </div>

                    <div class="h-4 w-px bg-slate-300 dark:bg-slate-700 hidden sm:block"></div>

                    <!-- Filtro por Estado -->
                    <div class="flex items-center gap-2">
                        <label for="filtroEstado" class="text-xs font-medium text-slate-600 dark:text-slate-400">Estado:</label>
                        <select 
                            id="filtroEstado" 
                            wire:model.live="filtroEstado" 
                            class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 py-1.5 px-2.5 shadow-2xs"
                        >
                            <option value="">Todos</option>
                            <option value="1">Solo Activos</option>
                            <option value="0">Solo Inactivos</option>
                        </select>
                    </div>
                </div>

                <!-- Derecha: Buscador con Debounce en Tiempo Real -->
                <div class="relative w-full md:w-80">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </div>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Buscar por sede, ciudad, teléfono..." 
                        class="w-full pl-9 pr-8 py-2 text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-2xs"
                    />
                    @if ($search !== '')
                        <button 
                            wire:click="$set('search', '')" 
                            type="button" 
                            class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                            title="Limpiar búsqueda"
                        >
                            <i class="fas fa-times-circle text-xs"></i>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Card Body: Tabla Responsiva -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">#</th>
                        <th class="py-3 px-4">Sede / Sucursal</th>
                        <th class="py-3 px-4">Ciudad / Dirección</th>
                        <th class="py-3 px-4">Contacto</th>
                        <th class="py-3 px-4 text-center">Registros Vinculados</th>
                        <th class="py-3 px-4 text-center">Estado</th>
                        <th class="py-3 px-4 text-center w-32">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse ($sucursales as $sucursal)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-3.5 px-4 text-center text-slate-400 dark:text-slate-500 font-mono text-[11px]">
                                {{ $sucursal->id }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 dark:text-white text-sm">
                                    {{ $sucursal->nombre }}
                                </div>
                                <span class="text-[11px] text-slate-400 dark:text-slate-500">
                                    Creado el {{ $sucursal->created_at?->format('d/m/Y') ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-1.5 font-medium text-slate-800 dark:text-slate-200">
                                    <i class="fas fa-map-marker-alt text-rose-500 text-xs"></i>
                                    <span>{{ $sucursal->ciudad ?: 'Sin ciudad registrada' }}</span>
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate max-w-xs mt-0.5">
                                    {{ $sucursal->direccion ?: 'Sin dirección especificada' }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if ($sucursal->telefono)
                                    <div class="flex items-center gap-1.5 text-slate-700 dark:text-slate-300">
                                        <i class="fas fa-phone-alt text-teal-500 text-[10px]"></i>
                                        <span class="font-mono text-xs">{{ $sucursal->telefono }}</span>
                                    </div>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500 italic text-[11px]">Sin teléfono</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex items-center gap-1.5 bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded-md text-[11px]">
                                    <span title="Personal" class="text-blue-600 dark:text-blue-400 font-semibold">
                                        <i class="fas fa-users text-[10px] me-0.5"></i>{{ $sucursal->users_count }}
                                    </span>
                                    <span class="text-slate-300 dark:text-slate-600">|</span>
                                    <span title="Lotes de Inventario" class="text-emerald-600 dark:text-emerald-400 font-semibold">
                                        <i class="fas fa-boxes text-[10px] me-0.5"></i>{{ $sucursal->lotes_count }}
                                    </span>
                                    <span class="text-slate-300 dark:text-slate-600">|</span>
                                    <span title="Proformas" class="text-purple-600 dark:text-purple-400 font-semibold">
                                        <i class="fas fa-file-medical text-[10px] me-0.5"></i>{{ $sucursal->proformas_count }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <button 
                                    wire:click="toggleEstado({{ $sucursal->id }})" 
                                    wire:loading.attr="disabled"
                                    type="button" 
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold transition-transform active:scale-95 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed {{ $sucursal->estado ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300' }}"
                                    title="Click para cambiar estado"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full {{ $sucursal->estado ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                    {{ $sucursal->estado ? 'Activa' : 'Inactiva' }}
                                </button>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button 
                                        wire:click="abrirModalEditar({{ $sucursal->id }})" 
                                        type="button" 
                                        class="p-1.5 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-slate-800 rounded-md transition-colors"
                                        title="Editar Sucursal"
                                    >
                                        <i class="fas fa-edit text-sm"></i>
                                    </button>

                                    <button 
                                        type="button" 
                                        @click="$dispatch('swal:confirm', {
                                            title: '¿Eliminar sucursal \'{{ addslashes($sucursal->nombre) }}\'?',
                                            text: 'Se validará que no posea personal, stock ni proformas asociadas.',
                                            icon: 'warning',
                                            confirmButtonText: 'Sí, eliminar',
                                            cancelButtonText: 'Cancelar',
                                            event: 'eliminarSucursal',
                                            params: {{ $sucursal->id }}
                                        })"
                                        class="p-1.5 text-rose-600 hover:text-rose-800 dark:text-rose-400 dark:hover:text-rose-300 hover:bg-rose-50 dark:hover:bg-slate-800 rounded-md transition-colors cursor-pointer"
                                        title="Eliminar Sucursal"
                                    >
                                        <i class="fas fa-trash-alt text-sm"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 px-4 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="h-16 w-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 text-2xl mb-3">
                                        <i class="fas fa-hospital-alt"></i>
                                    </div>
                                    <p class="text-base font-semibold text-slate-700 dark:text-slate-200">No se encontraron sucursales</p>
                                    <p class="text-xs text-slate-400 max-w-sm mt-1">
                                        @if ($search !== '')
                                            No hay sedes que coincidan con el término "{{ $search }}". Intente con otro criterio.
                                        @else
                                            No existen sucursales registradas aún. Comience agregando la sede central.
                                        @endif
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if ($sucursales->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                {{ $sucursales->links() }}
            </div>
        @endif
    </div>

    <!-- Modal de Creación / Edición de Sucursal -->
    @if ($modalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-lg">
                    <!-- Modal Header -->
                    <div class="px-6 py-4 bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-800/80 dark:to-slate-800/40 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 text-white text-sm">
                                <i class="fas {{ $sucursalId ? 'fa-edit' : 'fa-plus' }}"></i>
                            </span>
                            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100" id="modal-title">
                                {{ $sucursalId ? 'Editar Sucursal' : 'Nueva Sucursal' }}
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

                    <!-- Modal Body / Form -->
                    <form wire:submit="guardar" class="p-6 space-y-4">
                        <!-- Nombre -->
                        <div>
                            <label for="nombre" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Nombre de la Sede <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="nombre" 
                                wire:model="nombre" 
                                placeholder="Ej. Sede Central, Sucursal Norte"
                                class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-2.5 shadow-2xs @error('nombre') border-rose-500 @enderror"
                            />
                            @error('nombre')
                                <p class="text-[11px] text-rose-500 mt-1 flex items-center gap-1">
                                    <i class="fas fa-exclamation-circle text-[10px]"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Ciudad y Teléfono (2 columnas) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="ciudad" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                    Ciudad
                                </label>
                                <input 
                                    type="text" 
                                    id="ciudad" 
                                    wire:model="ciudad" 
                                    placeholder="Ej. Santa Cruz, La Paz"
                                    class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-2.5 shadow-2xs @error('ciudad') border-rose-500 @enderror"
                                />
                                @error('ciudad')
                                    <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="telefono" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                    Teléfono / Celular
                                </label>
                                <input 
                                    type="text" 
                                    id="telefono" 
                                    wire:model="telefono" 
                                    placeholder="Ej. 3-334455, 77001122"
                                    class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-2.5 shadow-2xs @error('telefono') border-rose-500 @enderror"
                                />
                                @error('telefono')
                                    <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Dirección -->
                        <div>
                            <label for="direccion" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Dirección Física
                            </label>
                            <input 
                                type="text" 
                                id="direccion" 
                                wire:model="direccion" 
                                placeholder="Ej. Av. Principal #123, Zona Centro"
                                class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-2.5 shadow-2xs @error('direccion') border-rose-500 @enderror"
                            />
                            @error('direccion')
                                <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Estado Activo Toggle -->
                        <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-800">
                            <div>
                                <span class="text-xs font-semibold text-slate-800 dark:text-slate-200 block">Sede Operativa Activa</span>
                                <span class="text-[11px] text-slate-400">Permite registrar personal, inventarios y proformas en esta sede.</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="estado" class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-hidden peer-focus:ring-2 peer-focus:ring-blue-500 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <!-- Modal Actions -->
                        <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2.5">
                            <button 
                                wire:click="cerrarModal" 
                                type="button" 
                                class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                            >
                                Cancelar
                            </button>
                            <button 
                                type="submit" 
                                wire:loading.attr="disabled"
                                wire:target="guardar"
                                class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                            >
                                <span wire:loading.remove wire:target="guardar">
                                    <i class="fas fa-save me-1"></i> Guardar Sucursal
                                </span>
                                <span wire:loading wire:target="guardar" class="inline-flex items-center gap-1.5">
                                    <i class="fas fa-spinner fa-spin"></i> Guardando...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
