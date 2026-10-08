# Plan de Integración: Actividad 3 (Consulta de Auditoría en Panel de Administrador)

Este documento detalla el plan técnico paso a paso con los bloques de código exactos para implementar la visualización de la tabla de auditoría de inicios de sesión en el panel de Administrador.

---

## 1. Controlador de Auditoría

Crearemos el controlador encargado de consultar los registros en la base de datos con paginación y optimización de consultas (*Eager Loading* de la relación `user`).

**Ruta del archivo:** `app/Http/Controllers/Admin/LoginAuditController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginAudit;
use Illuminate\View\View;

class LoginAuditController extends Controller
{
    public function index(): View
    {
        // Traer auditorías con su usuario asociado, ordenadas de más reciente a más antigua
        $audits = LoginAudit::with('user')
            ->latest('login_at')
            ->paginate(15);

        return view('admin.audits.index', compact('audits'));
    }
}
```

---

## 2. Definición de la Ruta

Registraremos la ruta dentro del grupo protegido de Administrador en `routes/admin.php`. De esta forma queda automáticamente restringida al rol `admin` mediante el middleware `role:admin`.

**Archivo a modificar:** `routes/admin.php`

```php
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoginAuditController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/attendances', [AttendanceController::class, 'index'])->name('attendances.index');
    
    // Ruta de consulta de auditoría
    Route::get('/audits', [LoginAuditController::class, 'index'])->name('audits.index');
});
```

---

## 3. Vista Blade (Interfaz de Usuario)

Crearemos la interfaz gráfica que sigue el mismo estándar visual de Tailwind CSS y tarjetas que el resto del sistema.

**Ruta del archivo:** `resources/views/admin/audits/index.blade.php`

```html
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
                <p class="text-sm text-slate-500 mt-1">Registro cronológico de los accesos realizados por los usuarios al sistema.</p>
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
                                    {{ ($audit->user->role ?? '') === 'admin' ? 'bg-purple-100 text-purple-700' : (($audit->user->role ?? '') === 'hr' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-700') }}">
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
                                No se encontraron registros de auditoría en el sistema.
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
```

---

## 4. Enlace en el Menú Lateral (Sidebar)

Agregaremos el acceso directo al menú lateral visible exclusivamente para usuarios con rol `admin`.

**Archivo a modificar:** `resources/views/layouts/partials/sidebar-nav.blade.php`

```html
@if($role === 'admin')
    <a href="{{ route('admin.audits.index') }}"
        class="flex items-center px-3 py-2.5 rounded-lg font-medium transition-colors {{ request()->routeIs('admin.audits.*') ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600 hover:bg-slate-50 hover:text-indigo-600' }}">
        <i data-feather="shield" class="w-5 h-5 mr-3"></i>
        Auditoría de Inicios
    </a>
@endif
```
