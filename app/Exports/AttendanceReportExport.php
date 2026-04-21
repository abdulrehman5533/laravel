<?php

namespace App\Exports;

use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AttendanceReportExport implements FromCollection, WithHeadings, WithMapping
{
    protected $month;
    protected $year;
    protected $branchId;
    protected $dates;
    protected $weeklyOffDays;

    public function __construct($month, $year, $branchId)
    {
        $this->month = $month;
        $this->year = $year;
        $this->branchId = $branchId;
        
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $this->dates[] = Carbon::createFromDate($year, $month, $i)->toDateString();
        }

        $defaultRule = \App\Models\HR\AttendanceRule::where('is_default', true)->first();
        $this->weeklyOffDays = $defaultRule ? ($defaultRule->weekly_off_days ?? []) : ['Sunday'];
    }

    public function collection()
    {
        return Employee::when($this->branchId, function($q) {
            return $q->where('branch_id', $this->branchId);
        })->with(['attendance' => function($q) {
            $q->whereMonth('date', $this->month)->whereYear('date', $this->year);
        }])->get();
    }

    public function headings(): array
    {
        $headings = ['Employee Name', 'Employee Code'];
        foreach ($this->dates as $date) {
            $headings[] = Carbon::parse($date)->format('d');
        }
        $headings = array_merge($headings, ['P', 'A', 'L', 'W', 'OT (Hrs)']);
        return $headings;
    }

    public function map($employee): array
    {
        $row = [$employee->first_name . ' ' . $employee->last_name, $employee->employee_code];
        
        $attendanceData = $employee->attendance->groupBy(fn($q) => $q->date->toDateString());
        
        $p = 0; $a = 0; $l = 0; $hd = 0; $ol = 0; $ot = 0; $w = 0;

        foreach ($this->dates as $date) {
            $att = $attendanceData->get($date)?->first();
            $dayName = Carbon::parse($date)->format('l');

            if ($att) {
                $status = $att->status;
            } elseif (in_array($dayName, $this->weeklyOffDays)) {
                $status = 'weekly_off';
            } else {
                $status = 'absent';
            }
            
            $row[] = $this->getStatusAbbreviation($status);

            if ($status === 'present') $p++;
            elseif ($status === 'absent') $a++;
            elseif ($status === 'late') $l++;
            elseif ($status === 'half_day') $hd++;
            elseif ($status === 'on_leave') $ol++;
            elseif ($status === 'weekly_off') $w++;

            if ($att) {
                $ot += $att->overtime_minutes;
            }
        }

        $row[] = $p;
        $row[] = $a;
        $row[] = $l;
        $row[] = $w;
        $row[] = round($ot / 60, 2);

        return $row;
    }

    protected function getStatusAbbreviation($status)
    {
        return match ($status) {
            'present' => 'P',
            'absent' => 'A',
            'late' => 'L',
            'half_day' => 'HD',
            'on_leave' => 'OL',
            'holiday' => 'H',
            'weekly_off' => 'W',
            default => '-',
        };
    }
}
