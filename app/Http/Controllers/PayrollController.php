<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Faculty;
use App\Models\Attendance;

class PayrollController extends Controller
{
    
    public function getFacultyExtraLoad(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if (!$startDate || !$endDate) {
            return response()->json(['message' => 'Invalid input. Please provide start and end dates.'], 400);
        }

        $facultyExtraLoad = DB::table('faculty_rates as fr')
            ->join('faculty as f', 'fr.faculty_id', '=', 'f.id')
            ->join('employment as e', 'fr.faculty_id', '=', 'e.faculty_id')
            ->leftJoin('attendance as a', function ($join) {
                $join->on('fr.faculty_id', '=', 'a.faculty_id');
            })
            ->leftJoin('schedule as s', function ($join) {
                $join->on('fr.faculty_id', '=', 's.faculty_id')
                    ->on(DB::raw("STR_TO_DATE(a.date, '%m/%d/%Y')"), '>=', DB::raw('s.date_from'))
                    ->on(DB::raw("STR_TO_DATE(a.date, '%m/%d/%Y')"), '<=', DB::raw('s.date_to'));
            })
            ->select(
                'fr.faculty_id',
                DB::raw('CONCAT(f.first_name, " ", f.last_name) as full_name'),
                'fr.rate_value',
                DB::raw('SUM(COALESCE(a.hours_worked, 0)) as total_hours_worked'),
                'a.time_in',
                's.time_start',
                'a.date',
                DB::raw("SEC_TO_TIME(SUM(
                CASE 
                    WHEN a.time_in IS NOT NULL AND s.time_start IS NOT NULL 
                    THEN GREATEST(0, TIMESTAMPDIFF(SECOND, s.time_start, a.time_in))
                    ELSE 0 
                END
            )) AS total_late")
            )
            ->where('e.employment_type', 'full_time')
            ->whereBetween(DB::raw("STR_TO_DATE(a.date, '%m/%d/%Y')"), [$startDate, $endDate])
            ->groupBy('fr.faculty_id', 'f.first_name', 'f.last_name', 'fr.rate_value', 'a.time_in', 's.time_start', 'a.date')
            ->get();

        if ($facultyExtraLoad->isEmpty()) {
            return response()->json(['message' => 'No extra load records found for the selected faculties.'], 404);
        }

        $response = $facultyExtraLoad->groupBy('faculty_id')->map(function ($records) use ($startDate, $endDate) {
            $totalLateMinutes = 0;
            $totalHoursWorked = $records->sum('total_hours_worked');

            foreach ($records as $faculty) {
                if (!empty($faculty->time_in) && !empty($faculty->time_start)) {
                    $timeIn = strtotime($faculty->time_in);
                    $timeStart = strtotime($faculty->time_start);

                    if ($timeIn > $timeStart) {
                        $late = ($timeIn - $timeStart) / 60;
                        $totalLateMinutes += $late;
                    }
                }
            }

            $lateHours = floor($totalLateMinutes / 60);
            $lateMinutes = $totalLateMinutes % 60;
            $formattedTotalLate = sprintf("%d:%02d", $lateHours, $lateMinutes);

            // Deduction logic based on date range
            $daysDiff = \Carbon\Carbon::parse($endDate)->diffInDays(\Carbon\Carbon::parse($startDate));
            $deductionThreshold = 0;

            if ($daysDiff <= 7) {
                $deductionThreshold = 24; // 1 week threshold
            } elseif ($daysDiff <= 15) {
                $deductionThreshold = 48; // 2 weeks threshold
            } elseif ($daysDiff <= 30) {
                $deductionThreshold = 96; // 1 month threshold
            }

            $totalHoursDeducted = max(0, $totalHoursWorked - $deductionThreshold);

            return [
                'faculty_id' => $records->first()->faculty_id,
                'full_name' => $records->first()->full_name,
                'rate_value' => $records->first()->rate_value,
                'total_hours_worked' => $totalHoursWorked,
                'total_late' => $totalLateMinutes > 0 ? $formattedTotalLate : '0:00',
                'total_hours_deducted' => $totalHoursDeducted,
            ];
        });

        return response()->json($response->values());
    }

    
    public function getPartTimeFacultyPayroll(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if (!$startDate || !$endDate) {
            return response()->json(['message' => 'Invalid input. Please provide start and end dates.'], 400);
        }

        $facultyPayroll = DB::table('faculty_rates as fr')
            ->join('faculty as f', 'fr.faculty_id', '=', 'f.id')
            ->join('employment as e', 'fr.faculty_id', '=', 'e.faculty_id')
            ->leftJoin('attendance as a', function ($join) {
                $join->on('fr.faculty_id', '=', 'a.faculty_id');
            })
            ->leftJoin('schedule as s', function ($join) {
                $join->on('fr.faculty_id', '=', 's.faculty_id')
                    ->on(DB::raw("STR_TO_DATE(a.date, '%m/%d/%Y')"), '>=', DB::raw('s.date_from'))
                    ->on(DB::raw("STR_TO_DATE(a.date, '%m/%d/%Y')"), '<=', DB::raw('s.date_to'));
            })
            ->select(
                'fr.faculty_id',
                DB::raw('CONCAT(f.first_name, " ", f.last_name) as full_name'),
                'fr.rate_value',
                DB::raw('SUM(COALESCE(a.hours_worked, 0)) as total_hours_worked'),
                'a.time_in',  
                's.time_start',  
                'a.date'  
            )
            ->where('e.employment_type', 'part_time')
            ->whereBetween(DB::raw("STR_TO_DATE(a.date, '%m/%d/%Y')"), [$startDate, $endDate])
            ->groupBy('fr.faculty_id', 'f.first_name', 'f.last_name', 'fr.rate_value', 'a.time_in', 's.time_start', 'a.date')
            ->get();

        if ($facultyPayroll->isEmpty()) {
            return response()->json(['message' => 'No payroll records found for the selected faculties.'], 404);
        }

        $response = $facultyPayroll->groupBy('faculty_id')->map(function ($records) {
            $totalLateMinutes = 0;

            foreach ($records as $faculty) { 
                if (!empty($faculty->time_in) && !empty($faculty->time_start)) {
                    $timeIn = strtotime($faculty->time_in);
                    $timeStart = strtotime($faculty->time_start);
 
                    if ($timeIn > $timeStart) {
                        $late = ($timeIn - $timeStart) / 60; 
                        $totalLateMinutes += $late;
                    }
                }
            }
 
            $lateHours = floor($totalLateMinutes / 60);
            $lateMinutes = $totalLateMinutes % 60;
            $formattedTotalLate = sprintf("%d:%02d", $lateHours, $lateMinutes);

            return [
                'faculty_id' => $records->first()->faculty_id,
                'full_name' => $records->first()->full_name,
                'rate_value' => $records->first()->rate_value,
                'total_hours_worked' => $records->sum('total_hours_worked'),
                'total_late' => $totalLateMinutes > 0 ? $formattedTotalLate : '0:00',  
            ];
        });

        return response()->json($response->values());
    }

    public function getFacultyAttendanceDetails(Request $request, $facultyId)
    { 
        $attendanceDetails = DB::table('attendance')
            ->join('faculty', 'attendance.faculty_id', '=', 'faculty.id')
            ->where('attendance.faculty_id', $facultyId)
            ->select(
                'attendance.faculty_id',
                'faculty.first_name',
                'faculty.last_name',
                DB::raw("STR_TO_DATE(attendance.date, '%m/%d/%Y') as converted_date"),
                DB::raw("IFNULL(TIME(STR_TO_DATE(attendance.time_in, '%h:%i %p')), '00:00:00') as time_only_in"),
                DB::raw("IFNULL(TIME(STR_TO_DATE(attendance.time_out, '%m/%d/%Y %h:%i %p')), '00:00:00') as time_only_out"),
                'attendance.hours_worked',
                'attendance.late',
                'attendance.absent',
                'attendance.overbreak',
                'attendance.ot',
                'attendance.undertime',
                'attendance.night_differential',
                'attendance.nd_ot',
                'attendance.remarks'
            )
            ->get();
 
        if ($attendanceDetails->isEmpty()) {
            return response()->json(['message' => 'No attendance details found.'], 404);
        }
 
        $schedules = DB::table('schedule')
            ->where('faculty_id', $facultyId)
            ->select('date_from', 'date_to', 'time_start', 'time_end')
            ->get();
 
        $formattedAttendance = $attendanceDetails->map(function ($att) use ($schedules) {
            $status = 'Unknown';

            if ($att->remarks === 'RESTDAY') {
                $status = 'RESTDAY';
            } elseif ($att->absent === '1') {
                $status = 'Absent';
            } elseif ($att->absent === '0') {
                $status = 'Present';
            }
 
            $schedule = $schedules->firstWhere(function ($sched) use ($att) {
                return $sched->date_from <= $att->converted_date && $sched->date_to >= $att->converted_date;
            });
 
            $late = 0;
            if ($schedule) {
                if (strtotime($att->time_only_in) > strtotime($schedule->time_start)) {
                    $late = (strtotime($att->time_only_in) - strtotime($schedule->time_start)) / 60; // in minutes
                }
            }
 
            $lateHours = floor($late / 60);
            $lateMinutes = $late % 60;
            $formattedLate = sprintf("%d:%02d", $lateHours, $lateMinutes);

            return [
                'faculty_id' => $att->faculty_id,
                'full_name' => $att->first_name . ' ' . $att->last_name,  
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
                'remarks' => $att->remarks  
            ];
        });
 
        return response()->json($formattedAttendance);
    }
    

    public function getFullTimeFacultyPayroll(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $attendanceData = DB::select("
                WITH AttendanceData AS (
                    SELECT 
                        a.faculty_id,
                        CONCAT(f.first_name,  ' ',
                               f.last_name) AS full_name,
                        COUNT(CASE 
                            WHEN CAST(a.absent AS UNSIGNED) = 0 THEN 1 
                        END) - COUNT(CASE 
                            WHEN a.remarks = 'RESTDAY' THEN 1 
                        END) AS total_present,
                        COUNT(CASE 
                            WHEN CAST(a.absent AS UNSIGNED) = 1 THEN 1 
                        END) AS total_absent,
                        COUNT(CASE 
                            WHEN a.remarks = 'RESTDAY' THEN 1 
                        END) AS total_restdays,
                        SEC_TO_TIME(SUM(
                            CASE 
                                WHEN TIME_TO_SEC(TIMEDIFF(a.time_in, s.time_start)) > 0 THEN 
                                    TIME_TO_SEC(TIMEDIFF(a.time_in, s.time_start))
                                ELSE 0
                            END
                        )) AS total_late_time
                    FROM 
                        attendance AS a
                    JOIN 
                        faculty AS f ON a.faculty_id = f.id
                    LEFT JOIN 
                        schedule AS s ON a.faculty_id = s.faculty_id 
                        AND STR_TO_DATE(a.date, '%m/%d/%Y') >= s.date_from
                        AND STR_TO_DATE(a.date, '%m/%d/%Y') <= s.date_to
                    WHERE 
                        STR_TO_DATE(a.date, '%m/%d/%Y') BETWEEN :start_date AND :end_date
                    GROUP BY 
                        a.faculty_id
                )
                SELECT 
                    ad.faculty_id,
                    ad.full_name,
                    e.employment_type,
                    e.status,
                    fr.rate_value,
                    fr.rate_type,
                    CASE 
                        WHEN fr.rate_type = 'baccalaureate' THEN 15000
                        WHEN fr.rate_type = 'master' THEN 20000
                        WHEN fr.rate_type = 'doctor' THEN 25000
                        ELSE 0
                    END AS monthly_rate,
                    ad.total_present,
                    ad.total_absent,
                    ad.total_restdays,
                    ad.total_late_time
                FROM 
                    AttendanceData AS ad
                JOIN 
                    employment AS e ON ad.faculty_id = e.faculty_id
                JOIN 
                    faculty_rates AS fr ON ad.faculty_id = fr.faculty_id
                WHERE 
                    e.employment_type = 'full_time' 
                    AND e.status = 'active'
                    AND fr.rate_type IN ('baccalaureate', 'master', 'doctor')
        ", [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

        return response()->json($attendanceData);
    }

    
}
