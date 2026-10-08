<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceReportController extends Controller
{
    public function index(Request $request): View
    {
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        $employees = User::where('role', 'employee')->get();

        $attendances = Attendance::selectRaw("
                user_id,
                COUNT(CASE WHEN status = 'hadir' THEN 1 END) as present,
                COUNT(CASE WHEN status = 'terlambat' THEN 1 END) as late,
                COUNT(CASE WHEN status = 'cuti' THEN 1 END) as cuti,
                COUNT(CASE WHEN status = 'sakit' THEN 1 END) as sick,
                COUNT(CASE WHEN status = 'alpha' THEN 1 END) as absent
            ")
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month)
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $summary = $employees->map(function ($employee) use ($attendances) {
            $data = $attendances->get($employee->id);
            return (object) [
                'name' => $employee->name,
                'present' => $data->present ?? 0,
                'late' => $data->late ?? 0,
                'cuti' => $data->cuti ?? 0,
                'sick' => $data->sick ?? 0,
                'absent' => $data->absent ?? 0,
            ];
        });

        $months = collect(range(1, 12))->mapWithKeys(fn($m) => [
            $m => Carbon::create()->month($m)->translatedFormat('F'),
        ]);

        $years = range(Carbon::now()->year - 2, Carbon::now()->year + 1);

        return view('reports.attendance', compact('summary', 'month', 'year', 'months', 'years'));
    }

    public function export(Request $request): StreamedResponse
    {
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        $employees = User::where('role', 'employee')->get();

        $attendances = Attendance::selectRaw("
                user_id,
                COUNT(CASE WHEN status = 'hadir' THEN 1 END) as present,
                COUNT(CASE WHEN status = 'terlambat' THEN 1 END) as late,
                COUNT(CASE WHEN status = 'cuti' THEN 1 END) as cuti,
                COUNT(CASE WHEN status = 'sakit' THEN 1 END) as sick,
                COUNT(CASE WHEN status = 'alpha' THEN 1 END) as absent
            ")
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month)
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $fileName = "reporte_asistencias_{$year}_{$month}.csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=\"{$fileName}\"",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0",
        ];

        $callback = function () use ($employees, $attendances) {
            $file = fopen('php://output', 'w');

            // BOM UTF-8 para compatibilidad con Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Encabezados del CSV
            fputcsv($file, ['Empleado', 'Presente (Hadir)', 'Tarde (Terlambat)', 'Vacaciones/Permiso (Cuti)', 'Enfermedad (Sakit)', 'Ausente (Alpha)']);

            // Filas de datos
            foreach ($employees as $employee) {
                $data = $attendances->get($employee->id);
                fputcsv($file, [
                    $employee->name,
                    $data->present ?? 0,
                    $data->late ?? 0,
                    $data->cuti ?? 0,
                    $data->sick ?? 0,
                    $data->absent ?? 0,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}