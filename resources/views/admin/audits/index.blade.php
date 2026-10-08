@extends('layouts.app')

@section('title', 'Auditoría de Inicios de Sesión')
@section('header_title', 'Auditoría de Accesos')

@section('content')
<div class="space-y-6">
    <!-- Encabezado y Descripción -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-slate-800">Historial de Inicios de Sesión</h2>
                <p class="text-sm text-slate-500 mt-1">Registro cronológico de los accesos de usuarios al sistema.</p>
            </div>
            <div class="flex items-center text-sm text-slate-500 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200">
                <i data-feather="database" class="w-4 h-4 mr-2 text-indigo-600"></i>
                Total de registros: <span class="font-semibold text-slate-700 ml-1">{{ $audits->total() }}</span>
            </div>
        </div>
    </div>

    <!-- Tabla de Registros -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Usuario</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Rol</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Dirección IP</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Fecha y Hora</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    @forelse($audits as $audit)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-slate-800">{{ $audit->user->name ?? 'Usuario Eliminado' }}</div>
                                <div class="text-xs text-slate-500">{{ $audit->user->email ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full 
                                    @if(($audit->user->role ?? '') === 'admin')
                                        bg-purple-100 text-purple-700
                                    @elseif(($audit->user->role ?? '') === 'hr')
                                        bg-blue-100 text-blue-700
                                    @else
                                        bg-slate-100 text-slate-700
                                    @endif">
                                    {{ strtoupper($audit->user->role ?? 'N/A') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-slate-600">
                                {{ $audit->ip_address }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                {{ $audit->login_at ? $audit->login_at->format('d/m/Y H:i:s') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-sm text-slate-500">
                                <div class="flex flex-col items-center justify-center">
                                    <i data-feather="inbox" class="w-8 h-8 text-slate-400 mb-2"></i>
                                    <span>No se encontraron registros de auditoría en el sistema.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($audits->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $audits->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
