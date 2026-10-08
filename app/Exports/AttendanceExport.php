<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $month;
    protected $year;

    public function __construct($month, $year)
    {
        $this->month = $month;
        $this->year = $year;
    }

    public function collection(): \Illuminate\Support\Enumerable
    {
        return Attendance::with('user')
            ->whereHas('user', function($q) {
                $q->where('role', 'employee');
            })
            ->whereYear('attendance_date', $this->year)
            ->whereMonth('attendance_date', $this->month)
            ->orderBy('attendance_date', 'asc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Empleado',
            'Fecha',
            'Hora de ingreso',
            'Hora de salida',
            'Estado de asistencia'
        ];
    }

    public function map($attendance): array
    {
        $checkIn = $attendance->check_in ? Carbon::parse($attendance->check_in)->format('H:i') : '--';
        $checkOut = $attendance->check_out ? Carbon::parse($attendance->check_out)->format('H:i') : '--';

        return [
            $attendance->user->name ?? 'N/A',
            Carbon::parse($attendance->attendance_date)->format('Y-m-d'),
            $checkIn,
            $checkOut,
            ucfirst($attendance->status)
        ];
    }

    public function styles(Worksheet $sheet): ?array
    {
        // Centrar todas las celdas de manera horizontal y vertical
        $sheet->getStyle($sheet->calculateWorksheetDimension())->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($sheet->calculateWorksheetDimension())->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // Encabezados en negrita con un fondo sutil opcional (solo lo dejamos en negrita)
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);

        return null;
    }
}
