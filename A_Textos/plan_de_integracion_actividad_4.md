# Plan de Integración: Actividad 4 (Exportación del Reporte de Asistencia a CSV)

Este documento detalla el plan técnico paso a paso para incorporar la descarga del reporte mensual de asistencias en formato CSV de manera eficiente mediante *Streaming* nativo de PHP/Laravel.

---

## 1. Definición de la Ruta de Exportación

Se añade la ruta encargada de procesar y descargar el archivo CSV dentro del grupo de reportes protegido (`role:admin,hr`).

**Archivo a modificar:** `routes/reports.php`

```php
use App\Http\Controllers\Report\AttendanceReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin,hr'])->prefix('reports')->name('reports.')->group(function () {
    Route::get('/attendance', [AttendanceReportController::class, 'index'])->name('attendance');
    // Nueva ruta para la descarga del CSV
    Route::get('/attendance/export', [AttendanceReportController::class, 'export'])->name('attendance.export');
});
```

---

## 2. Método de Exportación en el Controlador

Se implementa el método `export()` utilizando `response()->stream()` con `fputcsv()`. Esta técnica no almacena archivos temporales en el disco ni satura la memoria RAM del servidor, ya que escribe los datos directamente en el flujo de salida HTTP (*php://output*).

**Archivo a modificar:** `app/Http/Controllers/Report/AttendanceReportController.php`

**Paso A:** Asegurar la importación de `StreamedResponse`:
```php
use Symfony\Component\HttpFoundation\StreamedResponse;
```

**Paso B:** Agregar el método `export()`:
```php
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
            "Expires"             => "0"
        ];

        $callback = function () use ($employees, $attendances) {
            $file = fopen('php://output', 'w');
            
            // Agregar BOM UTF-8 para compatibilidad perfecta con Microsoft Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Encabezados de las columnas
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
```

---

## 3. Modificación de la Vista Blade (Botón de Descarga)

Se añade el botón **"Exportar CSV"** junto al botón de filtrado en la vista del reporte mensual.

**Archivo a modificar:** `resources/views/reports/attendance.blade.php`

```html
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
```
