<?php

namespace App\Imports;

use App\Models\Schedule;
use App\Models\Faculty;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ScheduleImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        Log::info('Processing row: ' . json_encode($row));

        // Create or get the faculty
        $faculty = Faculty::firstOrCreate(
            [
                'first_name' => ucfirst(strtolower($row['faculty_first_name'] ?? '')),
                'last_name' => ucfirst(strtolower($row['faculty_last_name'] ?? '')),
            ],
            [
                'designation' => 'full_time',
                'status' => 'active',
            ]
        );

        try {
            // Convert Excel serial date numbers to Carbon dates
            $dateFrom = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['date_from'] ?? '');
            $dateFromFormatted = Carbon::instance($dateFrom)->toDateString();

            $dateTo = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['date_to'] ?? '');
            $dateToFormatted = Carbon::instance($dateTo)->toDateString();
        } catch (\Exception $e) {
            Log::error('Error parsing date: ' . $e->getMessage());
            return null; // Skip row if date parsing fails
        }

        // Convert decimal times to time strings
        $timeStart = isset($row['time_start']) ? $this->decimalToTime($row['time_start']) : null;
        $timeEnd = isset($row['time_end']) ? $this->decimalToTime($row['time_end']) : null;

        // Check for existing schedule
        $existingSchedule = Schedule::where('faculty_id', $faculty->id)
                                    ->whereDate('date_from', $dateFromFormatted)
                                    ->first();

        if ($existingSchedule) {
            Log::info('Duplicate schedule found for faculty: ' . $faculty->id . ', date: ' . $dateFromFormatted);
            return null; // Skip row if duplicate schedule exists
        }

        return new Schedule([
            'faculty_id' => $faculty->id,
            'date_from' => $dateFromFormatted,
            'date_to' => $dateToFormatted,
            'time_start' => $timeStart,
            'time_end' => $timeEnd,
            'loading' => $row['loading'] ?? null,
        ]);
    }

    /**
     * Convert decimal representation of time to H:i:s format.
     *
     * @param float $decimalTime
     * @return string
     */
    protected function decimalToTime(float $decimalTime): string
    {
        $hours = (int)($decimalTime * 24);
        $minutes = (int)(($decimalTime * 24 - $hours) * 60);
        $seconds = (int)((($decimalTime * 24 - $hours) * 60 - $minutes) * 60);

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
}
