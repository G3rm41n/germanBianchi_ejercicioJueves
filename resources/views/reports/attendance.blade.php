@extends('layouts.app')

@section('title', 'Laporan Absensi')
@section('header_title', 'Laporan Absensi')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Laporan Absensi Bulanan</h2>
            <p class="text-sm text-slate-500 mt-1">Rekap kehadiran karyawan per bulan.</p>
        </div>
    </div>

    <form method="GET" class="flex items-end gap-4">
        <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Bulan</label>
            <select name="month" class="rounded-lg border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                @foreach($months as $val => $label)
                    <option value="{{ $val }}" {{ $val == $month ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Tahun</label>
            <select name="year" class="rounded-lg border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                @foreach($years as $y)
                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition">
                Tampilkan
            </button>
            <a href="{{ route('reports.attendance.export', ['month' => $month, 'year' => $year]) }}" 
               class="px-4 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700 transition flex items-center shadow-sm">
                <i data-feather="download" class="w-4 h-4 mr-2"></i>
                Exportar CSV
            </a>
        </div>
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Employee</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Present</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Late</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Leave</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Sick</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Absent</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    @forelse($summary as $item)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">{{ $item->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-semibold text-emerald-600">{{ $item->present }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-semibold {{ $item->late > 0 ? 'text-amber-600' : 'text-slate-600' }}">{{ $item->late }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-semibold {{ $item->cuti > 0 ? 'text-indigo-600' : 'text-slate-600' }}">{{ $item->cuti }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-semibold {{ $item->sick > 0 ? 'text-rose-600' : 'text-slate-600' }}">{{ $item->sick }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-semibold {{ $item->absent > 0 ? 'text-red-600' : 'text-slate-600' }}">{{ $item->absent }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 text-sm">Belum ada data absensi bulan ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection