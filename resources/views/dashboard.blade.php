<x-app-layout>

    <!-- AdminLTE Small Boxes / Metric Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Box 1: Pacientes -->
        <div class="relative overflow-hidden rounded-xl bg-gradient-to-br from-blue-600 to-blue-700 text-white shadow-md p-4 flex flex-col justify-between group">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-blue-100">Total Pacientes</p>
                    <h3 class="text-2xl font-extrabold mt-1">1,248</h3>
                    <p class="text-[11px] text-blue-100 mt-1 flex items-center">
                        <i class="fas fa-arrow-up text-[10px] me-1"></i> +12% este mes
                    </p>
                </div>
                <div class="p-3 bg-white/10 rounded-xl group-hover:scale-110 transition-transform">
                    <i class="fas fa-user-injured text-2xl text-blue-100"></i>
                </div>
            </div>
            <a href="#" class="mt-4 pt-2 border-t border-white/20 text-xs font-semibold text-blue-100 hover:text-white flex items-center justify-between">
                <span>Ver directorio</span>
                <i class="fas fa-arrow-circle-right text-xs"></i>
            </a>
        </div>

        <!-- Box 2: Citas de Hoy -->
        <div class="relative overflow-hidden rounded-xl bg-gradient-to-br from-emerald-600 to-teal-700 text-white shadow-md p-4 flex flex-col justify-between group">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-emerald-100">Citas de Hoy</p>
                    <h3 class="text-2xl font-extrabold mt-1">34</h3>
                    <p class="text-[11px] text-emerald-100 mt-1 flex items-center">
                        <i class="fas fa-clock text-[10px] me-1"></i> 8 pendientes de atención
                    </p>
                </div>
                <div class="p-3 bg-white/10 rounded-xl group-hover:scale-110 transition-transform">
                    <i class="fas fa-calendar-check text-2xl text-emerald-100"></i>
                </div>
            </div>
            <a href="#" class="mt-4 pt-2 border-t border-white/20 text-xs font-semibold text-emerald-100 hover:text-white flex items-center justify-between">
                <span>Ver agenda de citas</span>
                <i class="fas fa-arrow-circle-right text-xs"></i>
            </a>
        </div>

        <!-- Box 3: Médicos Activos -->
        <div class="relative overflow-hidden rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-md p-4 flex flex-col justify-between group">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-amber-100">Médicos en Turno</p>
                    <h3 class="text-2xl font-extrabold mt-1">16</h3>
                    <p class="text-[11px] text-amber-100 mt-1 flex items-center">
                        <i class="fas fa-stethoscope text-[10px] me-1"></i> 6 Especialidades activas
                    </p>
                </div>
                <div class="p-3 bg-white/10 rounded-xl group-hover:scale-110 transition-transform">
                    <i class="fas fa-user-md text-2xl text-amber-100"></i>
                </div>
            </div>
            <a href="#" class="mt-4 pt-2 border-t border-white/20 text-xs font-semibold text-amber-100 hover:text-white flex items-center justify-between">
                <span>Ver médicos y turnos</span>
                <i class="fas fa-arrow-circle-right text-xs"></i>
            </a>
        </div>

        <!-- Box 4: Triajes / Consultas -->
        <div class="relative overflow-hidden rounded-xl bg-gradient-to-br from-purple-600 to-indigo-700 text-white shadow-md p-4 flex flex-col justify-between group">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-purple-100">Historias Clínicas</p>
                    <h3 class="text-2xl font-extrabold mt-1">982</h3>
                    <p class="text-[11px] text-purple-100 mt-1 flex items-center">
                        <i class="fas fa-notes-medical text-[10px] me-1"></i> Digitalizadas y activas
                    </p>
                </div>
                <div class="p-3 bg-white/10 rounded-xl group-hover:scale-110 transition-transform">
                    <i class="fas fa-file-medical-alt text-2xl text-purple-100"></i>
                </div>
            </div>
            <a href="#" class="mt-4 pt-2 border-t border-white/20 text-xs font-semibold text-purple-100 hover:text-white flex items-center justify-between">
                <span>Ver historias clínicas</span>
                <i class="fas fa-arrow-circle-right text-xs"></i>
            </a>
        </div>
    </div>

    <!-- Quick Actions Bar -->
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xs border border-gray-200 dark:border-slate-800 p-4 mb-6 transition-colors duration-200">
        <h3 class="text-xs font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider mb-3">Acciones Rápidas</h3>
        <div class="flex flex-wrap gap-2.5">
            <a href="#" class="inline-flex items-center px-3.5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-xs transition">
                <i class="fas fa-calendar-plus me-2 text-xs"></i> Agendar Nueva Cita
            </a>
            <a href="#" class="inline-flex items-center px-3.5 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-xs transition">
                <i class="fas fa-user-plus me-2 text-xs"></i> Registrar Paciente
            </a>
            <a href="#" class="inline-flex items-center px-3.5 py-2 rounded-lg bg-slate-700 hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700 text-white text-xs font-semibold shadow-xs transition">
                <i class="fas fa-heartbeat me-2 text-xs"></i> Tomar Triaje
            </a>
            <a href="#" class="inline-flex items-center px-3.5 py-2 rounded-lg bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-750 text-gray-700 dark:text-slate-200 text-xs font-semibold shadow-xs transition">
                <i class="fas fa-pills me-2 text-emerald-500"></i> Dispensar Receta
            </a>
        </div>
    </div>

    <!-- Main Grid: Citas del Día & Estado del Centro -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Card 1: Próximas Citas (AdminLTE Card Format) -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-xl shadow-xs border border-gray-200 dark:border-slate-800 overflow-hidden transition-colors duration-200">
            <!-- Card Header -->
            <div class="px-5 py-4 border-b border-gray-200 dark:border-slate-800 flex items-center justify-between bg-gray-50/50 dark:bg-slate-900/80">
                <h3 class="font-bold text-sm text-gray-800 dark:text-white flex items-center">
                    <i class="fas fa-calendar-day text-blue-600 dark:text-blue-400 me-2"></i>
                    Citas Programadas para Hoy
                </h3>
                <span class="text-xs px-2.5 py-0.5 rounded-full font-semibold bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                    5 en espera
                </span>
            </div>

            <!-- Card Body / Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-gray-600 dark:text-slate-300">
                    <thead class="bg-gray-50 dark:bg-slate-800/80 text-gray-500 dark:text-slate-400 uppercase tracking-wider text-[11px] border-b border-gray-200 dark:border-slate-800 font-semibold">
                        <tr>
                            <th class="px-4 py-3">Hora</th>
                            <th class="px-4 py-3">Paciente</th>
                            <th class="px-4 py-3">Médico / Especialidad</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-slate-800/50 transition">
                            <td class="px-4 py-3 font-semibold text-gray-800 dark:text-slate-200">08:30 AM</td>
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">María Rodríguez Cruz</td>
                            <td class="px-4 py-3">Dr. Carlos Morales <span class="text-gray-400 dark:text-slate-500 block text-[11px]">Medicina General</span></td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                                    En Triaje
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button class="px-2.5 py-1 rounded bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/60 font-semibold transition">
                                    Atender
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-slate-800/50 transition">
                            <td class="px-4 py-3 font-semibold text-gray-800 dark:text-slate-200">09:15 AM</td>
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">Juan Carlos Flores</td>
                            <td class="px-4 py-3">Dra. Elena Silva <span class="text-gray-400 dark:text-slate-500 block text-[11px]">Pediatría</span></td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 dark:bg-blue-950/60 text-blue-800 dark:text-blue-400 border border-blue-200 dark:border-blue-800">
                                    En Espera
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button class="px-2.5 py-1 rounded bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/60 font-semibold transition">
                                    Llamar
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-slate-800/50 transition">
                            <td class="px-4 py-3 font-semibold text-gray-800 dark:text-slate-200">10:00 AM</td>
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">Rosa Martínez Quispe</td>
                            <td class="px-4 py-3">Dr. Roberto Paz <span class="text-gray-400 dark:text-slate-500 block text-[11px]">Ginecología</span></td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                    Atendido
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button class="px-2.5 py-1 rounded bg-gray-100 dark:bg-slate-800 text-gray-600 dark:text-slate-300 hover:bg-gray-200 dark:hover:bg-slate-700 font-semibold transition">
                                    Receta
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Card Footer -->
            <div class="px-4 py-3 bg-gray-50 dark:bg-slate-900/80 border-t border-gray-100 dark:border-slate-800 text-right">
                <a href="#" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                    Ver todas las citas del día <i class="fas fa-chevron-right text-[10px] ms-1"></i>
                </a>
            </div>
        </div>

        <!-- Card 2: Información del Centro de Salud -->
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xs border border-gray-200 dark:border-slate-800 overflow-hidden flex flex-col transition-colors duration-200">
            <div class="px-5 py-4 border-b border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-900/80">
                <h3 class="font-bold text-sm text-gray-800 dark:text-white flex items-center">
                    <i class="fas fa-hospital text-teal-600 dark:text-teal-400 me-2"></i>
                    Estado del Centro
                </h3>
            </div>
            <div class="p-5 flex-1 space-y-4">
                <!-- Consultorios Ocupados -->
                <div>
                    <div class="flex justify-between text-xs font-semibold text-gray-700 dark:text-slate-300 mb-1">
                        <span>Consultorios Activos</span>
                        <span>8 / 10</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-slate-800 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: 80%"></div>
                    </div>
                </div>

                <!-- Capacidad de Espera -->
                <div>
                    <div class="flex justify-between text-xs font-semibold text-gray-700 dark:text-slate-300 mb-1">
                        <span>Capacidad Sala de Espera</span>
                        <span>45%</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-slate-800 rounded-full h-2">
                        <div class="bg-teal-500 h-2 rounded-full" style="width: 45%"></div>
                    </div>
                </div>

                <!-- Turnos de guardia -->
                <div class="pt-3 border-t border-gray-100 dark:border-slate-800">
                    <h4 class="text-xs font-bold text-gray-800 dark:text-slate-200 mb-2">Turnos de Hoy</h4>
                    <div class="space-y-2 text-xs text-gray-600 dark:text-slate-300">
                        <div class="flex justify-between items-center p-2 rounded bg-slate-50 dark:bg-slate-800/80 border border-slate-100 dark:border-slate-700">
                            <div>
                                <p class="font-semibold text-slate-800 dark:text-slate-200">Mañana (08:00 - 14:00)</p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">6 Médicos generales, 2 Pediatras</p>
                            </div>
                            <span class="px-2 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-400 text-[10px] font-bold border border-emerald-200 dark:border-emerald-800">Activo</span>
                        </div>
                        <div class="flex justify-between items-center p-2 rounded bg-slate-50 dark:bg-slate-800/80 border border-slate-100 dark:border-slate-700">
                            <div>
                                <p class="font-semibold text-slate-800 dark:text-slate-200">Tarde (14:00 - 20:00)</p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">4 Médicos generales, 1 Ginecólogo</p>
                            </div>
                            <span class="px-2 py-0.5 rounded bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-[10px] font-bold">Próximo</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
