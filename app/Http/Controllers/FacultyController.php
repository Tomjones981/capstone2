<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Faculty;
use App\Models\ContactDetails;
use App\Models\Department;
use App\Models\Employment;
use App\Models\Faculty_Rates;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
class FacultyController extends Controller
{
    public function createFacultyInfo(Request $request)
    {
        $departmentId = $request->input('department_id');
        if (!$departmentId) {
            return response()->json(['error' => 'Department ID is required'], 400);
        }
        $faculty = Faculty::create([
            'department_id' => $departmentId,
            'first_name' => $request->input('first_name'),
            'middle_name' => $request->input('middle_name'),
            'last_name' => $request->input('last_name'),
            'faculty_type' => $request->input('faculty_type')
        ]);

        $contactDetails = ContactDetails::create([
            'faculty_id' => $faculty->id,
            'phone_number' => $request->input('phone_number'),
            'email' => $request->input('email')
        ]);
        $employment = Employment::create([
            'faculty_id' => $faculty->id,
            'employment_type' => $request->input('employment_type'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'status' => 'active',
            'note' => $request->input('note')

        ]);

        $facultyRate = Faculty_Rates::create([
            'faculty_id' => $faculty->id,
            'rate_type' => $request->input('rate_type'),
            'rate_value' => $request->input('rate_value')
        ]);

        $unit = Unit::create([
            'faculty_id' => $faculty->id,
            'teaching_units' => $request->input('teaching_units')
        ]);

        return response()->json([
            'faculty' => $faculty,
            'contact_details' => $contactDetails,
            'department' => $departmentId,
            'employment' => $employment,
            'faculty_rate' => $facultyRate,
            'rate_type' => $facultyRate,
            'unit' => $unit
        ], 201);
    }



    public function fetchFacultyInfo()
    {
        $facultyList = Faculty::select(
            'faculty.id',
            \DB::raw("CONCAT(faculty.first_name, ' ', COALESCE(faculty.middle_name, ''), ' ', faculty.last_name) AS full_name"),
            'faculty.first_name',
            'faculty.middle_name',
            'faculty.last_name', 
            'faculty_rates.rate_type AS designation',
            'department.department_name AS department',
            'employment.employment_type AS employment_type',
            'employment.status',
            'contact_details.email'
        )
            ->join('faculty_rates', 'faculty.id', '=', 'faculty_rates.faculty_id')
            ->leftJoin('department', 'faculty.department_id', '=', 'department.id')
            ->leftJoin('employment', 'faculty.id', '=', 'employment.faculty_id')
            ->leftJoin('contact_details', 'faculty.id', '=', 'contact_details.faculty_id')
            ->orderBy('faculty.id', 'ASC')
            ->get();

        return response()->json($facultyList);
    }
    public function editFacultyInfo(Request $request, $id)
    {
        // Validate incoming request
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'rate_type' => 'required|string|max:255',
            'department_id' => 'required|integer|exists:department,id',
            'employment_type' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);
 
        $faculty = Faculty::findOrFail($id);
 
        $faculty->update([
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'],
            'last_name' => $validated['last_name'],
            'department_id' => $validated['department_id'],
        ]); 
        $faculty->facultyRates()->update([
            'rate_type' => $validated['rate_type'],
        ]);

        $faculty->employment()->update([
            'employment_type' => $validated['employment_type'],
            'status' => $validated['status'],
        ]);

        $faculty->contactDetails()->update([
            'email' => $validated['email'],
        ]);

        return response()->json(['message' => 'Faculty information updated successfully']);
    }



    public function showFacByID($id)
    {
        $faculty = Faculty::with([
            'department',
            'employment',
            'faculty_rates',
            'contact_details',
            'unit'
        ])
            ->where('id', $id)
            ->first();

        if (!$faculty) {
            return response()->json(['message' => 'Faculty not found'], 404);
        }

        $rate_type = optional($faculty->faculty_rates->first())->rate_type;
        $rate_value = optional($faculty->faculty_rates->first())->rate_value;

        return response()->json([
            'first_name' => $faculty->first_name,
            'middle_name' => $faculty->middle_name,
            'last_name' => $faculty->last_name,
            'faculty_type' => $faculty->faculty_type,
            'department_id' => optional($faculty->department)->id,
            'department_name' => optional($faculty->department)->department_name,
            'employment_type' => optional($faculty->employment)->employment_type,
            'start_date' => optional($faculty->employment)->start_date,
            'end_date' => optional($faculty->employment)->end_date,
            'status' => optional($faculty->employment)->status,
            'rate_type' => $rate_type,
            'rate_value' => $rate_value,
            'phone_number' => $faculty->contact_details->pluck('phone_number'),
            'email' => $faculty->contact_details->pluck('email'),
            'teaching_units' => $faculty->unit->pluck('teaching_units'),
        ]);
    }

    public function editFac($id)
    {
        $faculty = Faculty::with([
            'department',
            'employment',
            'faculty_rates.faculty_types',
            'contact_details',
            'unit'
        ])
            ->where('id', $id)
            ->first();

        if (!$faculty) {
            return response()->json(['message' => 'Faculty not found'], 404);
        }

        $rates = $faculty->faculty_rates->map(function ($rate) {
            return [
                'rate_type' => $rate->faculty_types->rate_type,
                'rate_value' => $rate->rate_value
            ];
        });

        return response()->json([
            'first_name' => $faculty->first_name,
            'middle_name' => $faculty->middle_name,
            'last_name' => $faculty->last_name,
            'department_name' => $faculty->department->department_name,
            'employment_type' => $faculty->employment->employment_type ?? '',
            'start_date' => $faculty->employment->start_date ?? '',
            'end_date' => $faculty->employment->end_date ?? '',
            'status' => $faculty->employment->status ?? '',
            'rates' => $rates,
            'phone_number' => $faculty->contact_details->first()->phone_number ?? '',
            'email' => $faculty->contact_details->first()->email ?? '',
            'teaching_units' => $faculty->unit->first()->teaching_units ?? '',
        ]);
    }


    public function updateFaculty(Request $request, $id)
    {
        $faculty = Faculty::with([
            'employment',
            'faculty_rates',
            'contact_details',
            'unit'
        ])->find($id);

        if (!$faculty) {
            return response()->json(['message' => 'Faculty not found'], 404);
        }

        $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'faculty_type' => 'required|string|max:255',
            'department_id' => 'required|exists:department,id',
            'employment_type' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'status' => 'required|string',
            'rate_type' => 'required|string',
            'rate_value' => 'required|string',
            'phone_number' => 'required|string',
            'email' => 'required|string',
            'teaching_units' => 'required|string',
        ]);
 
        $faculty->update($request->only('first_name', 'middle_name', 'last_name','faculty_type',  'department_id'));
 
        if ($faculty->employment) {
            $faculty->employment()->update($request->only('employment_type', 'start_date', 'end_date', 'status'));
        } else {
            $faculty->employment()->create($request->only('employment_type', 'start_date', 'end_date', 'status'));
        }
 
        if ($faculty->faculty_rates) {
            $faculty->faculty_rates()->update($request->only('rate_type', 'rate_value'));
        } else {
            $faculty->faculty_rates()->create($request->only('rate_type', 'rate_value'));
        }
 
        $faculty->contact_details()->updateOrCreate(
            ['faculty_id' => $faculty->id],
            $request->only('phone_number', 'email')
        ); 
        $units = explode(',', $request->input('teaching_units'));
        $faculty->unit()->delete(); 
        foreach ($units as $unit) {
            Unit::create([
                'faculty_id' => $faculty->id,
                'teaching_units' => trim($unit)
            ]);
        }

        return response()->json(['message' => 'Faculty updated successfully']);
    }

    public function getFacultyList()
    {
        $faculty = Faculty::all();
        return response()->json($faculty);


    }







}
