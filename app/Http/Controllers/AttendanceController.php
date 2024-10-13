<?php

namespace App\Http\Controllers;
use App\Imports\AttendanceImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Models\Faculty;
use App\Models\Attendance;

class AttendanceController extends Controller
{
    public function importFacultyAttendance(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv'
        ]);

        Excel::import(new AttendanceImport, $request->file('file'));

        return response()->json(['message' => 'Attendance imported successfully'], 200);
    }

    // public function getFacultyAttendance(Request $request)
    // {
    //     $facultyIds = $request->input('facultyIds');
    //     $from = $request->input('from');
    //     $to = $request->input('to');

    //     if (!$facultyIds || !$from || !$to) {
    //         return response()->json(['message' => 'Invalid input. Please provide faculty IDs, from date, and to date.'], 400);
    //     }

    //     $facultyAttendance = Faculty::whereIn('id', $facultyIds)
    //         ->with([
    //             'attendance' => function ($query) use ($from, $to) {
    //                 $query->whereBetween(DB::raw("STR_TO_DATE(date, '%m/%d/%Y')"), [$from, $to])
    //                     ->select(
    //                         'faculty_id',
    //                         DB::raw("STR_TO_DATE(date, '%m/%d/%Y') as converted_date"),
    //                         DB::raw("IFNULL(TIME(STR_TO_DATE(time_in, '%h:%i %p')), '00:00:00') as time_only_in"),
    //                         DB::raw("IFNULL(TIME(STR_TO_DATE(time_out, '%m/%d/%Y %h:%i %p')), '00:00:00') as time_only_out"),
    //                         'hours_worked',
    //                         'late',
    //                         'absent',
    //                         'overbreak',
    //                         'ot',
    //                         'undertime',
    //                         'night_differential',
    //                         'nd_ot',
    //                         'remarks'
    //                     )
    //                     ->orderBy(DB::raw("STR_TO_DATE(date, '%m/%d/%Y')"), 'asc');
    //             }
    //         ])
    //         ->get();

    //     if ($facultyAttendance->isEmpty()) {
    //         return response()->json(['message' => 'No attendance records found for the selected faculties.'], 404);
    //     }

    //     $response = $facultyAttendance->map(function ($faculty) {
    //         return [
    //             'faculty_id' => $faculty->id,
    //             'full_name' => $faculty->first_name . ' ' . ($faculty->middle_name ? $faculty->middle_name . ' ' : '') . $faculty->last_name,
    //             'attendance' => $faculty->attendance->map(function ($att) use ($faculty) {
    //                 $schedule = $faculty->schedule->firstWhere('date', $att->converted_date);
    //                 $status = 'Unknown';
    //                 if ($att->remarks === 'RESTDAY') {
    //                     $status = 'RESTDAY';
    //                 } elseif ($att->absent === '1') {
    //                     $status = 'Absent';
    //                 } elseif ($att->absent === '0') {
    //                     $status = 'Present';
    //                 }

    //                 $late = 0;
    //                 if ($schedule && $att->time_only_in > $schedule->time_start) {
    //                     $late = (strtotime($att->time_only_in) - strtotime($schedule->time_start)) / 60;
    //                 }
    //                 return [
    //                     'date' => $att->converted_date,
    //                     'time_in' => $att->time_only_in,
    //                     'time_out' => $att->time_only_out,
    //                     'hours_worked' => $att->hours_worked,
    //                     'late' => $late > 0 ? $late : 0,
    //                     'absent' => $att->absent,
    //                     'status' => $status,
    //                     'overbreak' => $att->overbreak,
    //                     'ot' => $att->ot,
    //                     'undertime' => $att->undertime,
    //                     'night_differential' => $att->night_differential,
    //                     'nd_ot' => $att->nd_ot,
    //                     'remarks' => $att->remarks,
    //                 ];
    //             })
    //         ];
    //     });

    //     return response()->json($response);
    // }




    public function getFacultyAttendance(Request $request)
    {
        $facultyIds = $request->input('facultyIds');
        $from = $request->input('from');
        $to = $request->input('to');

        if (!$facultyIds || !$from || !$to) {
            return response()->json(['message' => 'Invalid input. Please provide faculty IDs, from date, and to date.'], 400);
        }

        $facultyAttendance = Faculty::whereIn('id', $facultyIds)
            ->with([
                'attendance' => function ($query) use ($from, $to) {
                    $query->whereBetween(DB::raw("STR_TO_DATE(date, '%m/%d/%Y')"), [$from, $to])
                        ->select(
                            'faculty_id',
                            DB::raw("STR_TO_DATE(date, '%m/%d/%Y') as converted_date"),
                            DB::raw("IFNULL(TIME(STR_TO_DATE(time_in, '%h:%i %p')), '00:00:00') as time_only_in"),
                            DB::raw("IFNULL(TIME(STR_TO_DATE(time_out, '%m/%d/%Y %h:%i %p')), '00:00:00') as time_only_out"),
                            'hours_worked',
                            'late',
                            'absent',
                            'overbreak',
                            'ot',
                            'undertime',
                            'night_differential',
                            'nd_ot',
                            'remarks'
                        )
                        ->orderBy(DB::raw("STR_TO_DATE(date, '%m/%d/%Y')"), 'asc');
                },
                'schedule' => function ($query) use ($from, $to) {
                    $query->whereDate('date_from', '<=', $to)
                        ->whereDate('date_to', '>=', $from)
                        ->select('faculty_id', 'date_from', 'date_to', 'time_start', 'time_end', 'loading');
                }
            ])
            ->get();

        if ($facultyAttendance->isEmpty()) {
            return response()->json(['message' => 'No attendance records found for the selected faculties.'], 404);
        }

        $response = $facultyAttendance->map(function ($faculty) {
            return [
                'faculty_id' => $faculty->id,
                'full_name' => $faculty->first_name . ' ' . ($faculty->middle_name ? $faculty->middle_name . ' ' : '') . $faculty->last_name,
                'attendance' => $faculty->attendance->map(function ($att) use ($faculty) {

                    $schedule = $faculty->schedule->firstWhere(function ($sched) use ($att) {
                        return $sched->date_from <= $att->converted_date && $sched->date_to >= $att->converted_date;
                    });

                    $status = 'Unknown';
                    if ($att->remarks === 'RESTDAY') {
                        $status = 'RESTDAY';
                    } elseif ($att->absent === '1') {
                        $status = 'Absent';
                    } elseif ($att->absent === '0') {
                        $status = 'Present';
                    }

                    $late = 0;
                    if ($schedule && strtotime($att->time_only_in) > strtotime($schedule->time_start)) {
                        $late = (strtotime($att->time_only_in) - strtotime($schedule->time_start)) / 60;
                    }
                    $lateHours = floor($late / 60);
                    $lateMinutes = $late % 60;
                    $formattedLate = sprintf("%d:%02d", $lateHours, $lateMinutes);
                    return [
                        'date' => $att->converted_date,
                        'time_in' => $att->time_only_in,
                        'time_out' => $att->time_only_out,
                        'hours_worked' => $att->hours_worked,
                        'late' => $late > 0 ? $formattedLate : '0:00',
                        'absent' => $att->absent,
                        'status' => $status,
                        'overbreak' => $att->overbreak,
                        'ot' => $att->ot,
                        'undertime' => $att->undertime,
                        'night_differential' => $att->night_differential,
                        'nd_ot' => $att->nd_ot,
                        'remarks' => $att->remarks,
                    ];
                })
            ];
        });

        return response()->json($response);
    }

}
