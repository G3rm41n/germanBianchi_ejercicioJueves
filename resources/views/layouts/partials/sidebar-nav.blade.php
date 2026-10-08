@auth
@php
    $role = Auth::user()->role;
@endphp

<nav class="space-y-1">
    <a href="{{ route($role . '.dashboard') }}"
        class="flex items-center px-3 py-2.5 rounded-lg font-medium transition-colors {{ request()->routeIs('*.dashboard') ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600 hover:bg-slate-50 hover:text-indigo-600' }}">
        <i data-feather="home" class="w-5 h-5 mr-3"></i>
        Dashboard
    </a>

    @if(in_array($role, ['admin', 'hr', 'employee']))
        @php
            $attendanceRoute = match ($role) {
                'admin' => 'admin.attendances.index',
                'hr' => 'hr.attendance.index',
                default => 'employee.attendance.index',
            };
        @endphp
        <a href="{{ route($attendanceRoute) }}"
            class="flex items-center px-3 py-2.5 rounded-lg font-medium transition-colors {{ request()->routeIs('admin.attendances.*', 'hr.attendance.*', 'employee.attendance.*') ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600 hover:bg-slate-50 hover:text-indigo-600' }}">
            <i data-feather="clock" class="w-5 h-5 mr-3"></i>
            Absensi
        </a>
    @endif

    @if($role === 'employee')
        <a href="{{ route('employee.leave-requests.index') }}"
            class="flex items-center px-3 py-2.5 rounded-lg font-medium transition-colors {{ request()->routeIs('employee.leave-requests.*') ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600 hover:bg-slate-50 hover:text-indigo-600' }}">
            <i data-feather="calendar" class="w-5 h-5 mr-3"></i>
            Pengajuan Cuti / Izin
        </a>
    @endif

    @if($role === 'hr')
        <a href="{{ route('hr.leave-management.index') }}"
            class="flex items-center px-3 py-2.5 rounded-lg font-medium transition-colors {{ request()->routeIs('hr.leave-management.*') ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600 hover:bg-slate-50 hover:text-indigo-600' }}">
            <i data-feather="calendar" class="w-5 h-5 mr-3"></i>
            Pengajuan Cuti & Izin
        </a>
    @endif

    @if(in_array($role, ['admin', 'hr']))
        <a href="{{ route('reports.attendance') }}"
            class="flex items-center px-3 py-2.5 rounded-lg font-medium transition-colors {{ request()->routeIs('reports.*') ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600 hover:bg-slate-50 hover:text-indigo-600' }}">
            <i data-feather="file-text" class="w-5 h-5 mr-3"></i>
            Laporan Absensi
        </a>
    @endif

    @if(in_array($role, ['admin', 'hr']))
        <div class="pt-4 mt-4 border-t border-slate-200">
            <p class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Manajemen</p>

            <a href="{{ route('management.departments.index') }}"
                class="flex items-center px-3 py-2.5 rounded-lg font-medium transition-colors {{ request()->routeIs('management.departments.*') ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600 hover:bg-slate-50 hover:text-indigo-600' }}">
                <i data-feather="briefcase" class="w-5 h-5 mr-3"></i>
                Departemen
            </a>

            @if($role === 'admin')
                <a href="{{ route('management.offices.index') }}"
                    class="flex items-center px-3 py-2.5 rounded-lg font-medium transition-colors {{ request()->routeIs('management.offices.*') ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600 hover:bg-slate-50 hover:text-indigo-600' }}">
                    <i data-feather="map-pin" class="w-5 h-5 mr-3"></i>
                    Kantor
                </a>

                <a href="{{ route('admin.audits.index') }}"
                    class="flex items-center px-3 py-2.5 rounded-lg font-medium transition-colors {{ request()->routeIs('admin.audits.*') ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600 hover:bg-slate-50 hover:text-indigo-600' }}">
                    <i data-feather="shield" class="w-5 h-5 mr-3"></i>
                    Audit Login
                </a>
            @endif

            <a href="{{ route('management.positions.index') }}"
                class="flex items-center px-3 py-2.5 rounded-lg font-medium transition-colors {{ request()->routeIs('management.positions.*') ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600 hover:bg-slate-50 hover:text-indigo-600' }}">
                <i data-feather="award" class="w-5 h-5 mr-3"></i>
                Jabatan
            </a>

            <a href="{{ route('management.employees.index') }}"
                class="flex items-center px-3 py-2.5 rounded-lg font-medium transition-colors {{ request()->routeIs('management.employees.*') ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600 hover:bg-slate-50 hover:text-indigo-600' }}">
                <i data-feather="users" class="w-5 h-5 mr-3"></i>
                Karyawan
            </a>

            <a href="{{ route('profile.edit') }}"
                class="flex items-center px-3 py-2.5 text-slate-600 hover:bg-slate-50 hover:text-indigo-600 rounded-lg font-medium transition-colors">
                <i data-feather="settings" class="w-5 h-5 mr-3"></i>
                Pengaturan
            </a>
        </div>
    @endif
</nav>
@endauth