<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Faculty;
class EmploymentController extends Controller
{
    public function fetchFacultyEmployment()
    {
        $facultyEmployment = Faculty::select(
            'faculty.id',
            \DB::raw("CONCAT(faculty.first_name, ' ', IFNULL(faculty.middle_name, ''), ' ', faculty.last_name) AS full_name"),
            'employment.employment_type',
            'employment.status',
            'department.department_name AS department'
        )
            ->leftJoin('employment', 'faculty.id', '=', 'employment.faculty_id')
            ->leftJoin('department', 'faculty.department_id', '=', 'department.id')
            ->orderBy('faculty.id', 'ASC')
            ->get();

        return response()->json($facultyEmployment);
    }
}
