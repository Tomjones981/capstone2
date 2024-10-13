<?php
namespace App\Imports;

use App\Models\Attendance;
use App\Models\Faculty;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class AttendanceImport implements ToModel, WithStartRow
{
    // Start importing data from row 7
    public function startRow(): int
    {
        return 7;
    }

    public function model(array $row)
    {
        // Assuming the columns in the Excel sheet are in this order:
        // 0: faculty_id, 1: date, 2: schedule, 3: remarks, 4: absent,
        // 5: time_in, 6: breaks, 7: time_out, 8: hours_worked,
        // 9: late, 10: overbreak, 11: ot, 12: undertime,
        // 13: night_differential, 14: nd_ot

        // Check if the faculty exists based on the faculty_id in the first column
        $faculty = Faculty::find($row[0]);

        if ($faculty) { 
            $existingAttendance = Attendance::where('faculty_id', $row[0])
                ->where('date', $row[1])
                ->first();
 
            if (!$existingAttendance) {
                return new Attendance([
                    'faculty_id'         => $row[0],
                    'date'               => $row[1],
                    'schedule'           => $row[2],
                    'remarks'            => $row[3],
                    'absent'             => $row[4],
                    'time_in'            => $row[5],
                    'breaks'             => $row[6],
                    'time_out'           => $row[7],
                    'hours_worked'       => $row[8],
                    'late'               => $row[9],
                    'overbreak'          => $row[10],
                    'ot'                 => $row[11],
                    'undertime'          => $row[12],
                    'night_differential' => $row[13],
                    'nd_ot'              => $row[14],
                ]);
            }
        }
    }
}

