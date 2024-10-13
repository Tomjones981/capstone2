<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Faculty;
use Illuminate\Http\Request;
use App\Imports\ScheduleImport;
use Maatwebsite\Excel\Facades\Excel;
use Exception;
class ScheduleController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        Excel::import(new ScheduleImport, $request->file('file'));

        return response()->json([
            'message' => 'Schedule data imported successfully!',
        ]);
    }

    // public function getFacultySched()
    // {
    //     $schedules = Schedule::with('faculty')->get();


    //     return response()->json($schedules);
    // }

    
    public function getFacultySchedule(Request $request)
    {
        $facultyIds = $request->input('facultyIds');
        $from = $request->input('from');
        $to = $request->input('to');
 
        if (!$facultyIds || !$from || !$to) {
            return response()->json(['message' => 'Invalid input. Please provide faculty IDs, from date, and to date.'], 400);
        }
 
        $facultySched = Faculty::whereIn('id', $facultyIds)
            ->with([
                'schedule' => function ($query) use ($from, $to) {
                    $query->whereBetween('date_from', [$from, $to])
                        ->orderBy('date_from', 'asc');
                }
            ])
            ->get();
 
        if ($facultySched->isEmpty()) {
            return response()->json(['message' => 'No schedule records found for the selected faculties.'], 404);
        } 
        $response = $facultySched->map(function ($faculty) {
            return [
                'faculty_id' => $faculty->id,
                'full_name' => $faculty->first_name . ' ' . ($faculty->middle_name ? $faculty->middle_name . ' ' : '') . $faculty->last_name,
                'schedule' => $faculty->schedule->map(function ($sched) {
                    return [
                        'date_from' => $sched->date_from,
                        'date_to' => $sched->date_to,
                        'time_start' => $sched->time_start, 
                        'time_end' => $sched->time_end, 
                        'loading' => $sched->loading, 
                    ];
                })
            ];
        });

        return response()->json($response);
    }
    public function destroy($id)
    {
        try {
            $schedule = Schedule::findOrFail($id);
            $schedule->delete();

            return response()->json([
                'message' => 'Schedule deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete schedule.',
                'error' => $e->getMessage()
            ], 500);
        }
    } 
    public function update(Request $request, $id)
    {
        $request->validate([
            'faculty_id' => 'required|exists:faculty,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date',
            'time_start' => 'required',
            'time_end' => 'required',
            'description' => 'required|string'
        ]);

        $schedule = Schedule::findOrFail($id);
        $schedule->update($request->all());
        return response()->json($schedule, 200);
    }

    public function delete($id)
    {
        Schedule::findOrFail($id)->delete();
        return response()->json(null, 204);
    }

    public function showByFaculty($id)
    {
        $schedule = Schedule::where('faculty_id', $id)->get();

        if ($schedule->isEmpty()) {
            return response()->json(['message' => 'No schedule found']);
        }

        return response()->json($schedule);
    }
    public function edit($id)
    {
        $schedule = Schedule::find($id);

        if (!$schedule) {
            return response()->json(['message' => 'Schedule not found'], 404);
        }

        return response()->json($schedule);
    }
    public function updateSched(Request $request, $id)
    {
        $schedule = Schedule::find($id);

        if (!$schedule) {
            return response()->json(['message' => 'Schedule not found'], 404);
        }

        $validatedData = $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date',
            'time_start' => 'required|date_format:H:i',
            'time_end' => 'required|date_format:H:i',
            'loading' => 'nullable|string',
        ]);

        $schedule->update($validatedData);

        return response()->json($schedule);
    }
    public function createSchedule(Request $request)
    { 
        $validatedData = $request->validate([
            'faculty_id' => 'required|exists:faculty,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date',
            'time_start' => 'required|date_format:H:i',
            'time_end' => 'required|date_format:H:i',
            'loading' => 'required|in:regular,overload',
        ]);
     
        $schedule = Schedule::create([
            'faculty_id' => $validatedData['faculty_id'],
            'date_from' => $validatedData['date_from'],
            'date_to' => $validatedData['date_to'],
            'time_start' => $validatedData['time_start'],
            'time_end' => $validatedData['time_end'],
            'loading' => $validatedData['loading'],
        ]);
    
        // Return a success response
        return response()->json(['message' => 'Schedule created successfully', 'schedule' => $schedule], 201);
    }



}
